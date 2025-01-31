<?php

namespace scheduler\controllers;

use Yii;
use yii\web\UploadedFile;
use scheduler\models\Appointments;
use scheduler\models\searches\AppointmentsSearch;
use scheduler\models\Availability;
use scheduler\models\SpaceAvailability;
use scheduler\models\Space;
use scheduler\models\AppointmentAttendees;
use scheduler\models\AppointmentAttachments;
use scheduler\hooks\TimeHelper;
use scheduler\hooks\AppointmentRescheduler as Ar;
use auth\models\User;
use scheduler\models\OperationReasons;
use scheduler\models\MeetingTypes;;

use app\providers\components\SmsComponent;

/**
 * @OA\Tag(
 *     name="Appointments",
 *     description="Available endpoints for Appointments model"
 * )
 */
class AppointmentsController extends \helpers\ApiController
{

    public $permissions = [
        'schedulerAppointmentsList' => 'View Appointments List',
        'schedulerAppointmentsCreate' => 'Add Appointments',
        'schedulerAppointmentsUpdate' => 'Edit Appointments',
        'schedulerAppointmentsDelete' => 'Delete Appointments',
        'schedulerAppointmentsRestore' => 'Restore Appointments',
        'schedulerAppointmentsCancel' => 'Cancel Appointments',
    ];

    public function actionIndex()
    {
        Yii::$app->user->can('schedulerAppointmentsList');

        $authManager = Yii::$app->authManager;
        $currentUserId = Yii::$app->user->id;
        $userRoles = array_keys($authManager->getRolesByUser($currentUserId));

        $isSuperAdmin = in_array('su', $userRoles);
        $isSecretary = in_array('secretary', $userRoles);

        $searchModel = new AppointmentsSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams, 'AppointmentsSearch');


        if ($isSecretary ||  $isSuperAdmin && isset($search['user_id']) && !empty($search['user_id'])) {
            $dataProvider = $searchModel->search($search);
        } else {
            $dataProvider = $searchModel->search($search);
            if (!$isSuperAdmin) {
                $dataProvider->query->andWhere(['user_id' => $currentUserId]);
            }
        }

        $appointments = $dataProvider->getModels();

        foreach ($appointments as &$appointment) {
            $appointmentData = $appointment->toArray();
            $appointmentData['userName'] = Appointments::getUserName($appointment->user_id);

            $space = SpaceAvailability::find()
                ->where(['appointment_id' => $appointment->id])
                ->asArray()
                ->one();

            if ($space && isset($space['space_id'])) {
                $spaceDetails = Space::getSpaceNameDetails($space['space_id']);
                $appointmentData['space'] = $spaceDetails;
            } else {
                $appointmentData['space'] = null;
            }

            $attendees = AppointmentAttendees::find()
                ->select(['attendee_id', 'status'])
                ->where(['appointment_id' => $appointment->id, 'is_removed' => 0])
                ->asArray()
                ->all();

            $attendeeDetails = [];
            foreach ($attendees as $attendee) {
                if (isset($attendee['attendee_id'])) {
                    $user = User::findOne($attendee['attendee_id']);
                    $attendeeDetails[] = [
                        'attendee_id' => $attendee['attendee_id'],
                        'email' => $user ? $user->profile->email_address : '',
                        'fullname' => $user ? $user->profile->first_name . ' ' . $user->profile->last_name : '',
                        'status' => $attendee['status']
                    ];
                }
            }

            $appointmentData['attendees'] = $attendeeDetails;

            $appointment = $appointmentData;
        }

        $dataProvider->setModels($appointments);

        return $this->payloadResponse($dataProvider, ['oneRecord' => false]);
    }

    public function actionPendingAppointments()
    {
        Yii::$app->user->can('registrar');

        $searchModel = new AppointmentsSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams, 'AppointmentsSearch');
        $dataProvider = $searchModel->search($search);

        $dataProvider->query->andWhere(['status' => Appointments::STATUS_PENDING]);
        $dataProvider->query->andWhere(['=', 'is_deleted', Appointments::STATUS_DELETED]);


        $appointments = $dataProvider->getModels();

        foreach ($appointments as &$appointment) {
            $appointmentData = $appointment->toArray();
            $appointmentData['userName'] = Appointments::getUserName($appointment->user_id);
            $appointment = $appointmentData;
        }

        $dataProvider->setModels($appointments);

        return $this->payloadResponse($dataProvider, ['oneRecord' => false]);
    }

    public function actionApprove($id)
    {
        Yii::$app->user->can('registrar');
        $model = $this->findModel($id);

        if ($model->status !== Appointments::STATUS_PENDING) {
            return $this->toastResponse(['statusCode' => 400, 'message' => 'Appointment cannot be approved. It may not exist or is not pending.']);
        }

        $model->appointment_date = date('Y-m-d', strtotime($model->appointment_date));

        $model->status = Appointments::STATUS_ACTIVE;

        if ($model->save(false)) {
            $model->sendAppointmentCreatedEvent(
                $model->id,
                $model->email_address,
                $model->contact_name,
                $model->user_id,
                $model->appointment_date,
                $model->start_time,
                $model->end_time
            );

            return $this->toastResponse(['message' => 'Appointment has been approved successfully.']);
        }

        return $this->toastResponse(['statusCode' => 500, 'message' => 'Failed to approve appointment']);
    }

    public function actionReject($id)
    {
        Yii::$app->user->can('registrar');
        $request = Yii::$app->request;
        $model = $this->findModel($id);

        if ($model->status !== Appointments::STATUS_PENDING) {
            return $this->toastResponse(['statusCode' => 400, 'message' => 'Appointment cannot be rejected. It may not exist or is not pending.']);
        }

        if ($request->isPut) {
            $putParams = $request->getBodyParams();
            $reason = isset($putParams['rejection_reason']) ? $putParams['rejection_reason'] : null;
            $model->rejection_reason = $reason;
        }

        $model->scenario = Appointments::SCENARIO_REJECT;

        if (!$model->validate()) {
            return $this->errorResponse($model->getErrors());
        }

        $model->appointment_date = date('Y-m-d', strtotime($model->appointment_date));
        $model->status = Appointments::STATUS_REJECTED;

        if ($model->save(false)) {
            // save action reason here
            $operationReason = new OperationReasons();

            if (!$operationReason->saveActionReason($model->id,  $model->rejection_reason, 'REJECTED', 'APPOINTMENTS', $model->user_id, Yii::$app->user->id)) {
                return $this->errorResponse(['message' => ['Unable to save rejection reason. Please try again later.']]);
            }

            $model->sendAppointmentRejectedEvent(
                $model->email_address,
                $model->contact_name,
                $model->user_id,
                $model->date,
                $model->start_time,
                $model->end_time
            );

            return $this->toastResponse(['statusCode' => 202, 'message' => 'Appointment has been rejected successfully.']);
        }

        return $this->toastResponse(['statusCode' => 500, 'message' => 'Failed to reject appointment.']);
    }

    public function actionView($id)
    {
        Yii::$app->user->can('schedulerAppointmentsList');

        $appointment = $this->findModel($id);

        $statusLabel = Appointments::getStatusLabel($appointment->status);

        $appointmentData = $appointment->toArray();
        $appointmentData['statusLabel'] = $statusLabel;

        $space = SpaceAvailability::find()
            ->where(['appointment_id' => $appointment->id])
            ->asArray()
            ->one();

        if ($space && isset($space['space_id'])) {
            $spaceDetails = Space::getSpaceNameDetails($space['space_id']);
            $appointmentData['space'] = $spaceDetails;
        } else {
            $appointmentData['space'] = null;
        }

        $attendees = AppointmentAttendees::find()
            ->select(['attendee_id', 'status'])
            ->where(['appointment_id' => $appointment->id, 'is_removed' => 0])
            ->asArray()
            ->all();

        $attendeeDetails = [];
        foreach ($attendees as $attendee) {
            if (isset($attendee['attendee_id'])) {
                $user = User::findOne($attendee['attendee_id']);
                $attendeeDetails[] = [
                    'attendee_id' => $attendee['attendee_id'],
                    'email' => $user ? $user->profile->email_address : '',
                    'fullname' => $user ? $user->profile->first_name . ' ' . $user->profile->last_name : '',
                    'status' => $attendee['status'],
                ];
            }
        }

        $file = AppointmentAttachments::getAppointmentAttachment($appointment->id);
        $appointmentData['attachment'] = $file;
        $appointmentData['attendees'] =  $attendeeDetails;

        return $this->payloadResponse($appointmentData);
    }

    public function actionMeetingTypes()
    {
        $model = new MeetingTypes();
        $appointmentTypes = $model->getAppointmentTypes();

        $types = [];
        foreach ($appointmentTypes as $appointmentType) {
            $types[] = $appointmentType->type;
        }
        return $this->payloadResponse(['types' => $types]);
    }

    public function actionCheckin($id)
    {
        // $isToggled = Appointments::toggleCheckedInAppointment($id);

        // if (!$isToggled) {
        //     return $this->errorResponse(['message' => 'Failed to toggle appointment status']);
        // }

        $checkinResponse = Appointments::checkedInAppointemnt($id);

        if (!$checkinResponse['success']) {
            // return $this->errorResponse(['message' => $checkinResponse['message']]);
            return $this->toastResponse(['statusCode' => 202, 'message' => $checkinResponse['message']]);
        }

        // $appointment = Appointments::findOne($id);
        // $message = $appointment->checked_in ? 'Appointment has been marked as Attended' : 'Appointment has been unchecked';

        return $this->toastResponse(['statusCode' => 202, 'message' => $checkinResponse['message']]);
    }

    public function actionCreate($dataRequest = null)
    {
        Yii::$app->user->can('schedulerAppointmentsCreate');
        $model = new Appointments();
        $model->loadDefaultValues();
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();

        if ($dataRequest['Appointments']['space_id'] === 'null') {
            $dataRequest['Appointments']['space_id'] = null;
        }

        $transaction = Yii::$app->db->beginTransaction();


        try {

            if ($model->load($dataRequest)) {
                $model->uploadedFile = UploadedFile::getInstanceByName('file');
                $model->attendees = $dataRequest['Appointments']['attendees'] ?? [];

                $model->space_id = $dataRequest['Appointments']['space_id'];

                if (!empty($dataRequest['Appointments']['space_id']) || $dataRequest['Appointments']['space_id'] !== null) {
                    $space = Space::findOne($dataRequest['Appointments']['space_id']);

                    if (!$space) {
                        return $this->errorResponse(['message' => ['The specified space does not exist']]);
                    }

                    $model->status = Appointments::STATUS_PENDING;
                } else {
                    $user_id =  $dataRequest['Appointments']['user_id'];
                    $space = Space::findOne(['id' => $user_id]);

                    if (!$space) {
                        return $this->errorResponse(['message' => ['User-specific office space is not configured']]);
                    }

                    $model->status = Appointments::STATUS_ACTIVE;
                }



                if (!$model->validate()) {
                    return $this->errorResponse($model->getErrors());
                }


                if ($model->save()) {

                    $this->saveAttendees($dataRequest, $model->id);
                    if (!empty($dataRequest['Appointments']['space_id'])) {
                        $this->saveSpaceAvailability($dataRequest, $model->id);
                    }

                    $uploadResult = $this->handleFileUpload($model->uploadedFile, $model->id);

                    if ($uploadResult !== true) {
                        return $this->errorResponse(['message' => $uploadResult['message']]);
                    }


                    if ($model->status === Appointments::STATUS_ACTIVE) {

                        $model->sendAppointmentCreatedEvent(
                            $model->id,
                            $model->email_address,
                            $model->contact_name,
                            $model->user_id,
                            $model->appointment_date,
                            $model->start_time,
                            $model->end_time
                        );

                        $transaction->commit();
                        return $this->payloadResponse($model, ['statusCode' => 201, 'message' => 'Appointment added successfully']);
                    } else {
                        $transaction->commit();
                        return $this->payloadResponse($model, ['statusCode' => 201, 'message' => 'Appointment created successfully, Pending Approval']);
                    }
                } else {
                    throw new \Exception('Failed to save appointment');
                }
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $this->errorResponse(['message' => [$e->getMessage()]]);
        }
    }

    public function actionUpdate($id)
    {
        Yii::$app->user->can('schedulerAppointmentsUpdate');
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();
        $dataRequest['Appointments']['appointment_date'] = date('Y-m-d', strtotime($dataRequest['Appointments']['appointment_date']));

        $model = $this->findModel($id);

        // getting initial appointments data
        $initial_date = $model->appointment_date;
        $initial_start_time =  $model->start_time;
        $initial_end_time = $model->end_time;

        $attendees = AppointmentAttendees::findAll(['appointment_id' => $id]);
        $spaceAvailability = SpaceAvailability::findOne(['appointment_id' => $id]);


        if ($model->load($dataRequest)) {

            $model->uploadedFile = UploadedFile::getInstanceByName('file');

            if (!$model->validate()) {
                return $this->errorResponse($model->getErrors());
            }

            if ($model->status === Appointments::STATUS_RESCHEDULE) {
                $model->status = Appointments::STATUS_RESCHEDULED;
            }

            $transaction = Yii::$app->db->beginTransaction();

            try {
                if ($model->save()) {
                    $this->updateAttendees($dataRequest, $attendees);
                    $this->updateSpaceAvailability($dataRequest, $spaceAvailability, $model->id);
                    $uploadResult = $this->handleFileUpload($model->uploadedFile, $model->id);

                    if ($uploadResult !== true) {
                        //todo: use toast response instead
                        return $this->errorResponse(['message' => $uploadResult['message']]);
                    }

                    if ($model->status === Appointments::STATUS_RESCHEDULED) {
                        $model->sendAppointmentRescheduledEvent(
                            $model->user_id,
                            $model->email_address,
                            $model->appointment_date,
                            $model->start_time,
                            $model->end_time,
                            $model->contact_name
                        );
                    }

                    // Send notifications if the appointment date has been updated
                    if (
                        $model->appointment_date !== $initial_date ||
                        $model->start_time !== $initial_start_time ||
                        $model->end_time !== $initial_end_time
                    ) {
                        $model->sendAppointmentDateUpdatedEvent(
                            $model->user_id,
                            $model->email_address,
                            $model->appointment_date,
                            $model->start_time,
                            $model->end_time,
                            $model->contact_name,
                            $model->created_at
                        );
                    }

                    $transaction->commit();

                    return $this->payloadResponse($this->findModel($id), ['statusCode' => 202, 'message' => 'Meeting details updated successfully']);
                } else {
                    throw new \Exception('Failed to save appointment');
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                return $this->errorResponse(['message' => [$e->getMessage()]]);
            }
        }
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            Yii::$app->user->can('schedulerAppointmentsRestore');
            $isAvailable = $this->checkAvailability(
                $model->user_id,
                $model->appointment_date,
                $model->start_time,
                $model->end_time
            );

            $appointmentExists = Appointments::hasOverlappingAppointment(
                $model->user_id,
                $model->appointment_date,
                $model->start_time,
                $model->end_time,
                $model->id // exclude current appointment from the check
            );
            if (!$isAvailable || $appointmentExists) {
                // return $this->errorResponse(['message' => ['The appointment cannot be restored because the time slot is no longer available.']]);
                return $this->toastResponse(['message' => 'The appointment cannot be restored because the time slot is no longer available.']);
            }
            $model->restore();
            return $this->toastResponse(['statusCode' => 202, 'message' => 'Appointments restored successfully']);
        } else {
            Yii::$app->user->can('schedulerAppointmentsDelete');
            $model->delete();
            return $this->toastResponse(['statusCode' => 202, 'message' => 'Appointments deleted successfully']);
        }
        return $this->errorResponse($model->getErrors());
    }

    public function actionCancel($id)
    {
        Yii::$app->user->can('schedulerAppointmentsCancel');

        $request = Yii::$app->request;

        $model = $this->findModel($id);
        $contact_email = $model->email_address;
        $contact_name = $model->contact_name;
        $date = $model->appointment_date;
        $starTime = $model->start_time;
        $endTime = $model->end_time;

        $user = User::findOne($model->user_id);

        if ($user && $user->profile) {
            $chairPersonEmail = $user->profile->email_address;
        } else {
            return $this->errorResponse(['message' => ['User profile or email not found']]);
        }

        // Set scenario to 'cancel' for validation
        $model->scenario = Appointments::SCENARIO_CANCEL;

        if ($request->isPut) {
            $putParams = $request->getBodyParams();
            $reason = isset($putParams['cancellation_reason']) ? $putParams['cancellation_reason'] : null;
        }

        $model->cancellation_reason = $reason;

        // Validate cancellation reason
        if (!$model->validate()) {
            return $this->errorResponse($model->getErrors());
        }

        $model->status = Appointments::STATUS_CANCELLED;

        if ($model->save(false)) {

            $operationReason = new OperationReasons();

            if (!$operationReason->saveActionReason($model->id,  $model->cancellation_reason, 'CANCELLED', 'APPOINTMENTS', $model->user_id, Yii::$app->user->id)) {
                return $this->errorResponse(['message' => ['Unable to save rejection reason. Please try again later.']]);
            }

            $model->sendAppointmentCancelledEvent($contact_email, $contact_name, $date, $starTime, $endTime, $chairPersonEmail, $model->user_id);

            return $this->toastResponse(['statusCode' => 202, 'message' => 'Appointments CANCELLED successfully']);
        }

        return $this->errorResponse($model->getErrors());
    }

    protected function findModel($id)
    {
        if (($model = Appointments::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException('Record not found.');
    }

    private function checkAvailability($user_id, $appointment_date, $start_time, $end_time)
    {
        $bookedSlots = Availability::getUnavailableSlots(
            $user_id,
            $appointment_date,
            $start_time,
            $end_time
        );

        if ($bookedSlots) {
            return false;
        }
        return true;
    }

    public function actionGetSlots($user_id = null, $date = null)
    {
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();
        $user_id = $dataRequest['Appointments']['user_id'];
        $date = $dataRequest['Appointments']['date'];


        if (empty($user_id) || empty($date)) {
            return $this->errorResponse(['message' => ['user id and date is required']]);
        }

        $slots = TimeHelper::getAvailableSlots($user_id, $date);
        return $this->payloadResponse(['slots' => $slots]);
    }

    public function actionSuggestAvailableSlots($id)
    {
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();
        $rescheduledAppointmentId = $id;


        if (empty($rescheduledAppointmentId)) {
            return $this->errorResponse(['message' => ['Appointment ID is required']]);
        }

        $model = new Appointments();
        $appoitment = $model->getRescheduledAppointment($rescheduledAppointmentId);

        if (!$appoitment) {
            $msg = 'Appointment with id {' . $rescheduledAppointmentId . '} not found';
            return $this->payloadResponse(['message' => $msg]);
        }

        $suggestions = Ar::findNextAvailableSlot(
            $appoitment->user_id,
            $appoitment->appointment_date,
            $appoitment->start_time,
            $appoitment->end_time
        );
        return $this->payloadResponse(['suggestions' => $suggestions]);
    }

    public function actionSpaceDetails($space_id = null, $date = null)
    {
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();
        $space_id = $dataRequest['Appointments']['space_id'];
        $date = $dataRequest['Appointments']['date'];

        if (empty($space_id) || empty($date)) {
            return $this->errorResponse(['message' => ['space id and date is required']]);
        }

        $space = Space::findOne($space_id);
        if (!$space) {
            return $this->payloadResponse(['message' => 'Space not found.']);
        }

        // Retrieve occupied time slots for the specified date
        $occupiedSlots = SpaceAvailability::getSpaceAvailability($space_id, $date);

        return $this->payloadResponse([
            'space' => [
                'opening time' => $space->opening_time,
                'closing_time' => $space->closing_time,
            ],
            'occupied_slots' => $occupiedSlots,
        ]);
    }

    protected function handleFileUpload($uploadedFile, $modelId)
    {
        if ($uploadedFile) {
            $attachmentModel = new AppointmentAttachments();
            $uploadResult = $attachmentModel->fileUpload($uploadedFile, $modelId);

            if ($uploadResult !== true) {
                return $uploadResult;
            }
        }

        return true;
    }

    protected function saveAttendees($dataRequest, $id)
    {
        $attendees = $dataRequest['Appointments']['attendees'] ?? [];
        $date = $dataRequest['Appointments']['appointment_date'];
        $startTime = $dataRequest['Appointments']['start_time'];
        $endTime = $dataRequest['Appointments']['end_time'];

        $userId = $dataRequest['Appointments']['user_id'] ?? null;

        if (is_string($attendees)) {
            $attendees = explode(',', $attendees);
        }

        // remove user-id ie the owner of the meeting from list of attendees
        // if ($userId !== null) {
        //     $attendees = array_filter($attendees, function ($attendeeId) use ($userId) {
        //         return trim($attendeeId) != $userId;
        //     });
        // }

        if ($userId !== null) {
            $attendees[] = $userId;
        }

        $attendees = array_unique(array_map('trim', $attendees));

        if (!empty($attendees)) {
            foreach ($attendees as $attendeeId) {
                $attendee_id = trim($attendeeId);
                $addAttendee = new AppointmentAttendees();
                $addAttendee->addAttendee($id, $attendee_id, $date, $startTime, $endTime);
            }
        }
    }

    // public function actionRemoveAttendee($id)
    // {
    //     $dataRequest['Attendee'] = Yii::$app->request->getBodyParams();
    //     $attendees = $dataRequest['Attendee']['Attendees'] ?? [];

    //     $model = new AppointmentAttendees();
    //     $model->scenario = AppointmentAttendees::SCENARIO_REMOVE;

    //     if (!$model->load($dataRequest, 'Attendees') || !$model->validate()) {
    //         return $this->errorResponse($model->getErrors());
    //     }

    //     $appointment = Appointments::findOne($id);
    //     if (!$appointment) {
    //         return $this->errorResponse(['message' => ['Appointment not found']]);
    //     }

    //     foreach ($attendees as $attendeeId => $removalReason) {
    //         $attendee = AppointmentAttendees::findOne([
    //             'appointment_id' => $id,
    //             'attendee_id' => $attendeeId,
    //         ]);

    //         if (!$attendee) {
    //             continue; 
    //         }

    //         $attendee->is_removed = AppointmentAttendees::STATUS_REMOVED;

    //         if ($attendee->save(false)) {
    //             $operationReason = new OperationReasons();

    //             $saved = $operationReason->saveActionReason($id, $removalReason,'REMOVED', 'APPOINTMENTS', $attendeeId, Yii::$app->user->id);

    //             if (!$saved) {
    //                 // Yii::error("Failed to save operation reason for attendee ID: {$attendeeId}");
    //                 return $this->errorResponse(['message' => ['Failed to save operation reason for attendee ID: {$attendeeId}']]);
    //             }

    //             // Send update notification/event
    //             $attendee->sendAttendeeUpdateEvent(
    //                 $attendee->appointment_id,
    //                 $attendee->id,
    //                 $removalReason,
    //                 true
    //             );
    //         } else {
    //             // Yii::error("Failed to remove attendee ID: {$attendeeId}");
    //             return $this->errorResponse(['message' => ['Failed to remove attendee ID: {$attendeeId}']]);

    //         }
    //     }

    //     return $this->toastResponse(['message' => ['Attendees processed successfully.']]);
    // }
    public function actionRemoveAttendee($id)
    {
        $dataRequest['Attendee'] = Yii::$app->request->getBodyParams();
        $attendees = $dataRequest['Attendee']['Attendees'] ?? [];

        $model = new AppointmentAttendees();

        if (!$model->load($dataRequest, 'Attendees') || !$model->validate()) {
            return $this->errorResponse($model->getErrors());
        }

        $appointment = Appointments::findOne($id);
        if (!$appointment) {
            return $this->errorResponse(['message' => ['Appointment not found']]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $successCount = 0;
            $failureCount = 0;

            foreach ($attendees as $attendeeId => $removalReason) {
                $attendee = AppointmentAttendees::findOne([
                    'appointment_id' => $id,
                    'staff_id' => $attendeeId,
                ]);

                if (!$attendee) {
                    $failureCount++;
                    continue;
                }

                $attendee->is_removed = AppointmentAttendees::STATUS_REMOVED;

                if ($attendee->save(false)) {
                    $operationReason = new OperationReasons();
                    $saved = $operationReason->saveActionReason(
                        $id,
                        $removalReason,
                        'REMOVED',
                        'APPOINTMENTS',
                        $attendeeId,
                        Yii::$app->user->id
                    );

                    if (!$saved) {
                        $transaction->rollBack();
                        return $this->errorResponse(['message' => ["Failed to save operation reason for attendee ID: $attendeeId"]]);
                    }

                    $attendee->sendAttendeeUpdateEvent(
                        $attendee->appointment_id,
                        $attendee->id,
                        $removalReason,
                        true
                    );
                    $successCount++;
                } else {
                    $failureCount++;
                }
            }

            $transaction->commit();
            return $this->toastResponse([
                'message' => [
                    "$successCount attendees processed successfully.",
                    "$failureCount attendees failed to be processed."
                ]
            ]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $this->errorResponse(['message' => ['An unexpected error occurred.']]);
        }
    }


    protected function saveSpaceAvailability($dataRequest, $appointmentId)
    {
        $model = new SpaceAvailability();

        $model->space_id = $dataRequest['Appointments']['space_id'];
        $model->appointment_id =  $appointmentId;
        $model->date = $dataRequest['Appointments']['appointment_date'];
        $model->start_time = $dataRequest['Appointments']['start_time'];
        $model->end_time = $dataRequest['Appointments']['end_time'];

        if (!$model->save()) {
            throw new \Exception('Failed to save space availability: ' . implode(', ', $model->getErrorSummary(true)));
        }
    }

    protected function updateAttendees($dataRequest, $currentAttendees)
    {
        if ($dataRequest['Appointments']['attendees'] === 'null') {
            $dataRequest['Appointments']['attendees'] = null;
        }

        $newAttendeesData = $dataRequest['Appointments']['attendees'];

        if (empty($newAttendeesData) || $newAttendeesData === null) {
            return;
        }

        $existingAttendeesIds = array_column($currentAttendees, 'id');

        foreach ($newAttendeesData as $attendeeData) {
            if (in_array($attendeeData['staff_id'], $existingAttendeesIds)) {
                $attendee = AppointmentAttendees::findOne($attendeeData['staff_id']);
                $attendee->attributes = $attendeeData;

                return $attendee;

                unset($attendee->appointment_id);

                if (!$attendee->validate() || !$attendee->save()) {
                    return $this->errorResponse($attendee->getErrors());
                }
            } else {
                $newAttendee = new AppointmentAttendees();
                $newAttendee->appointment_id = $dataRequest['Appointments']['id'];
                $newAttendee->attributes = $attendeeData;
                if (!$newAttendee->validate() || !$newAttendee->save()) {
                    return $this->errorResponse($newAttendee->getErrors());
                }

                $newAttendee->sendAttendeeUpdateEvent($newAttendee->appointment_id, $newAttendee->id);
            }
        }
    }

    protected function updateSpaceAvailability($dataRequest, $currentSpaceAvailability)
    {
        if (empty($dataRequest['Appointments']['space_id'] === 'null')) {
            $dataRequest['Appointments']['space_id'] === null;
        }

        $space_id = $dataRequest['Appointments']['space_id'];
        $date = $dataRequest['Appointments']['space_id'];
        $start_time = $dataRequest['Appointments']['space_id'];
        $end_time = $dataRequest['Appointments']['space_id'];


        if (empty($space_id) || $space_id === null) {
            return;
        }

        $original_venue = $currentSpaceAvailability ? $currentSpaceAvailability->space_id : null;
        $new_venue = $space_id;

        if ($currentSpaceAvailability) {

            $currentSpaceAvailability->space_id = $space_id;

            if (!$currentSpaceAvailability->validate() || !$currentSpaceAvailability->save()) {
                return $this->errorResponse($currentSpaceAvailability->getErrors());
            }
        } else {

            $newSpaceAvailability = new SpaceAvailability();
            $newSpaceAvailability->space_id = $space_id;
            $newSpaceAvailability->date = $date;
            $newSpaceAvailability->start_time = $start_time;
            $newSpaceAvailability->end_time = $end_time;

            if (!$newSpaceAvailability->validate() || !$newSpaceAvailability->save()) {
                return $this->errorResponse($currentSpaceAvailability->getErrors());
            }
        }

        if ($original_venue !== $new_venue) {

            $model = new Appointments();
            $model->sendAppointmentDateUpdatedEvent(
                $model->user_id,
                $model->email_address,
                $model->appointment_date,
                $model->start_time,
                $model->end_time,
                $model->contact_name,
                $model->created_at
            );
        }
    }

    public function actionConfirmAttendance($appointment_id, $attendee_id)
    {
        $dataRequest['Attendance'] = Yii::$app->request->getBodyParams();

        $confirmationAnswer = $dataRequest['Attendance']['confirmation_answer'];
        $declineReason = $dataRequest['Attendance']['decline_reason'];

        if (!isset($confirmationAnswer)) {
            return $this->errorResponse(['message' => ['Invalid data submitted.']]);
        }

        $attendee = $this->findAttendee($appointment_id, $attendee_id);
        if (!$attendee) {
            return $this->errorResponse(['message' => ['Invalid confirmation link.']]);
        }

        $appointment = Appointments::findOne($appointment_id);

        if (!$appointment) {
            return $this->errorResponse(['message' => ['Meeting not found.']]);
        }

        if ($appointment->status == Appointments::STATUS_CANCELLED) {
            return $this->toastResponse(['message' => ['This meeting has already been cancelled.']]);
        }

        // Check if the attendee is the chairperson
        $isChairperson = $appointment->user_id == $attendee_id;
        $statusMessage = $this->processConfirmation($confirmationAnswer, $attendee, $appointment, $isChairperson, $declineReason);


        if ($attendee->save()) {
            return $this->toastResponse([
                'statusCode' => 200,
                'message' => $statusMessage,
            ]);
        } else {
            return $this->errorResponse(['message' => ['Unable to process your response. Please try again later.']]);
        }
    }

    private function findAttendee($appointmentId, $attendeeId)
    {
        return AppointmentAttendees::findOne([
            'appointment_id' => $appointmentId,
            'attendee_id' => $attendeeId,
        ]);
    }

    private function processConfirmation($confirmationAnswer, $attendee, $appointment, $isChairperson, $declineReason)
    {
        if ($confirmationAnswer === true) {
            $attendee->status = AppointmentAttendees::STATUS_CONFIRMED;
            $attendee->save(false);
            return 'Your attendance has been confirmed. Thank you!';
        } else {
            $attendee->status = AppointmentAttendees::STATUS_DECLINED;

            $statusMessage = 'Your attendance has been declined. Thank you for your response!';

            $operationalReason = new OperationReasons();

            if (!$operationalReason->saveActionReason($appointment->id, $declineReason, 'DECLINED', 'APPOINTMENTS', $$attendee->id, $attendee->id)) {
                return $this->errorResponse(['message' => ['Unable to save decline reason. Please try again later.']]);
            }

            if ($isChairperson) {
                $this->handleChairpersonDecline($appointment, $declineReason, $attendee->attendee_id);
            }

            $attendee->save(false);
            return $statusMessage;
        }
    }

    private function handleChairpersonDecline($appointment, $declineReason, $attendeeId)
    {
        $appointment->status = Appointments::STATUS_CANCELLED;
        $appointment->cancelation_reason = $declineReason;

        if (!$appointment->save(false)) {
            return false;
        }

        $operationalReason = new OperationReasons();

        if (!$operationalReason->saveActionReason($appointment->id, $declineReason, 'CANCELLED', 'APPOINTMENTS', $$attendeeId, $attendeeId)) {
            return $this->errorResponse(['message' => ['Unable to save decline reason. Please try again later.']]);
        }

        $appointment->sendAppointmentCancelledEvent(
            $appointment->email_address,
            $appointment->contact_name,
            $appointment->appointment_date,
            $appointment->start_time,
            $appointment->end_time,
            $this->getChairpersonEmail($appointment->user_id)
        );
    }

    private function getChairpersonEmail($userId)
    {
        $user = User::findOne($userId);
        return $user && $user->profile ? $user->profile->email_address : null;
    }

    /**
     * Recursively convert all values equal to 'null' (string) to null in an array.
     *
     * @param array $data The input array to process.
     * @return array The processed array with 'null' values converted to null.
     */
    protected function convertNullStringsToNull(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->convertNullStringsToNull($value);
            } elseif ($value === 'null') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    public function actionSendSms()
    {
        $to = '+254768810076';
        $message = 'Hello,Your appointment has been rescheduled to 12:00 PM on 2023-01-15. Please check your calendar for more details.';

        $sms = new SmsComponent();
        $response = $sms->send($to, $message);

        return $this->payloadResponse($response, ['statusCode' => 202, 'message' => 'Message sent successfully']);
    }
}
