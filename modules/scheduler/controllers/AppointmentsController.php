<?php

namespace scheduler\controllers;

use Yii;
use yii\web\UploadedFile;
use scheduler\models\Appointments;
use scheduler\models\AppointmentType;
use scheduler\models\searches\AppointmentsSearch;
use scheduler\models\Availability;
use scheduler\models\Events;
use scheduler\models\SpaceAvailability;
use scheduler\models\Space;
use scheduler\models\AppointmentAttendees;
use scheduler\models\AppointmentAttachments;
use scheduler\hooks\TimeHelper;
use scheduler\hooks\AppointmentRescheduler as Ar;
use auth\models\User;
use auth\models\Profiles;
use helpers\traits\AppointmentPolicy;

/**
 * @OA\Tag(
 *     name="Appointments",
 *     description="Available endpoints for Appointments model"
 * )
 */
class AppointmentsController extends \helpers\ApiController
{

    use AppointmentPolicy;

    public $permissions = [
        'schedulerAppointmentsList' => 'View Appointments List',
        'schedulerAppointmentsCreate' => 'Add Appointments',
        'schedulerAppointmentsUpdate' => 'Edit Appointments',
        'schedulerAppointmentsDelete' => 'Delete Appointments',
        'schedulerAppointmentsRestore' => 'Restore Appointments',
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


        if ($isSecretary && isset($search['user_id']) && !empty($search['user_id'])) {
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
                ->where(['appointment_id' => $appointment->id])
                ->asArray()
                ->all();

            $appointmentData['attendees'] = $attendees;

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

            return $this->toastResponse(['statusCode' => 202, 'message' => 'Appointment has been approved successfully.']);
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

        $model->scenario = Appointments::SCENARIO_REJECT;

        if ($request->isPut) {
            $putParams = $request->getBodyParams();
            $reason = isset($putParams['rejection_reason']) ? $putParams['rejection_reason'] : null;
        }

        $model->rejection_reason = $reason;

        if (!$model->validate()) {
            return $this->errorResponse($model->getErrors());
        }

        $model->status = Appointments::STATUS_REJECTED;

        if ($model->save(false)) {
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
            ->where(['appointment_id' => $appointment->id])
            ->asArray()
            ->all();

        $file = AppointmentAttachments::getAppointmentAttachment($appointment->id);
        $appointmentData['attachment'] = $file;
        $appointmentData['attendees'] = $attendees;

        return $this->payloadResponse($appointmentData);
    }

    public function actionAppointmentsTypes()
    {
        $model = new AppointmentType();
        $appointmentTypes = $model->getAppointmentTypes();

        $types = [];
        foreach ($appointmentTypes as $appointmentType) {
            $types[] = $appointmentType->type;
        }
        return $this->payloadResponse(['types' => $types]);
    }

    public function actionGetPriorities()
    {
        $priorities = Appointments::getPriorityLabel();

        return $this->payloadResponse($priorities);
    }

    public function actionCheckin($id)
    {
        // Call the model method to toggle the checked_in status
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
        // Yii::$app->user->can('schedulerAppointmentsCreate');
        $model = new Appointments();
        $model->loadDefaultValues();
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();

        $transaction = Yii::$app->db->beginTransaction();

        try {

            if ($model->load($dataRequest)) {
                if (!$model->validate()) {
                    return $this->errorResponse($model->getErrors());
                }

                $uploadedFile = UploadedFile::getInstanceByName('file');

                $space = null;
                $levelName = null;

                if (!empty($dataRequest['Appointments']['space_id']) || $dataRequest['Appointments']['space_id'] !== null) {
                    $space = Space::findOne($dataRequest['Appointments']['space_id']);

                    if (!$space) {
                        return $this->payloadResponse(['message' => 'The specified space does not exist']);
                    }

                    $levelName = $space->level ? $space->level->name : null;
                }

                if ($space) {
                    if ($levelName === 'Level 4') {
                        $model->status = Appointments::STATUS_ACTIVE;
                    } else {
                        $model->status = Appointments::STATUS_PENDING;
                    }
                } else {
                    $model->status = Appointments::STATUS_ACTIVE;
                }

                if ($model->save()) {

                    $this->saveAttendees($dataRequest, $model->id);
                    if (!empty($dataRequest['Appointments']['space_id'])) {
                        $this->saveSpaceAvailability($dataRequest, $model->id);
                    }

                    $this->handleFileUpload($uploadedFile, $model->id);


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
        $model = $this->findModel($id);

        $attendees = AppointmentAttendees::findAll(['appointment_id' => $id]);
        $spaceAvailability = SpaceAvailability::findOne(['appointment_id' => $id]);

        if ($model->load($dataRequest)) {
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

                    $transaction->commit();

                    return $this->payloadResponse($this->findModel($id), ['statusCode' => 202, 'message' => 'Appointments updated successfully']);
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
                return $this->errorResponse(['message' => ['The appointment cannot be restored because the time slot is no longer available.']]);
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
        $request = Yii::$app->request;

        $model = $this->findModel($id);
        $contact_email = $model->email_address;
        $contact_name = $model->contact_name;
        $date = $model->appointment_date;
        $starTime = $model->start_time;
        $endTime = $model->end_time;

        $user = User::findOne($model->user_id);

        if ($user && $user->profile) {
            $bookedUserEmail = $user->profile->email_address;
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

        // Capture who canceled the appointment
        $currentUser = Yii::$app->user->identity;
        $cancelledBy = $currentUser->username;
        $cancelledByRole = $currentUser->can_be_booked ? 'VC/DVC' : 'Secretary';

        if ($model->save(false)) {

            $model->sendAppointmentCancelledEvent($contact_email, $contact_name, $date, $starTime, $endTime, $bookedUserEmail);
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
        $priority = $dataRequest['Appointments']['priority'] ?? null;


        if (empty($user_id) || empty($date)) {
            return $this->errorResponse(['message' => ['user id and date is required']]);
        }

        // Validate priority if it is provided
        // if ($priority !== null && !is_int($priority)) {
        //     return $this->errorResponse(['message' => ['Priority must be an integer']]);
        // }

        $slots = TimeHelper::getAvailableSlots($user_id, $date, $priority);
        return $this->payloadResponse(['slots' => $slots]);
    }

    public function actionSuggestAvailableSlots($id)
    {
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();
        // $rescheduledAppointmentId = $dataRequest['Appointments']['id'];
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
    }

    protected function saveAttendees($dataRequest, $id)
    {
        $attendees = $dataRequest['Appointments']['attendees'] ?? [];
        $date = $dataRequest['Appointments']['appointment_date'];
        $startTime = $dataRequest['Appointments']['start_time'];
        $endTime = $dataRequest['Appointments']['end_time'];

        if (is_string($attendees)) {
            $attendees = explode(',', $attendees);
        }

        $addAttendees = new AppointmentAttendees();

        if (!empty($attendees)) {
            foreach ($attendees as $attendeeId) {
                $staffId = trim($attendeeId);
                $addAttendees->addAttendee($id, $staffId, $date, $startTime, $endTime);
            }
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
        if (empty($dataRequest['Appointments']['attendees'])) {
            return;
        }

        $newAttendeesData = $dataRequest['Appointments']['attendees'];
        $existingAttendeesIds = array_column($currentAttendees, 'id');
        $newAttendeeIds = array_column($newAttendeesData, 'id');

        foreach ($currentAttendees as $attendee) {
            if (!in_array($attendee->id, $newAttendeeIds)) {
                $attendee->delete();
            }
        }

        foreach ($newAttendeesData as $attendeeData) {
            if (in_array($attendeeData['id'], $existingAttendeesIds)) {
                $attendee = AppointmentAttendees::findOne($attendeeData['id']);
                $attendee->attributes = $attendeeData;

                //Ensure that appointment_id is not modified in updates
                unset($attendee->appointment_id);

                if (!$attendee->validate() || !$attendee->save()) {
                    throw new \Exception('Failed to update attendee');
                }
            } else {
                $newAttendee = new AppointmentAttendees();
                $newAttendee->appointment_id = $dataRequest['Appointments']['id'];
                $newAttendee->attributes = $attendeeData;
                if (!$newAttendee->validate() || !$newAttendee->save()) {
                    throw new \Exception('Failed to add new attendee');
                }
            }
        }
    }

    protected function updateSpaceAvailability($dataRequest, $currentSpaceAvailability, $appointmentId)
    {
        if (empty($dataRequest['Appointments']['space'])) {
            throw new \Exception('No space data provided');
        }

        $spaceData = $dataRequest['Appointments']['space'];

        if ($currentSpaceAvailability) {
            $currentSpaceAvailability->attributes = $spaceData;
            $currentSpaceAvailability->appointment_id = $appointmentId;

            if (!$currentSpaceAvailability->validate() || !$currentSpaceAvailability->save()) {
                throw new \Exception('Failed to update space availability');
            }
        } else {
            $newSpaceAvailability = new SpaceAvailability();
            $newSpaceAvailability->attributes = $spaceData;
            $newSpaceAvailability->appointment_id = $appointmentId;

            if (!$newSpaceAvailability->validate() || !$newSpaceAvailability->save()) {
                throw new \Exception('Failed to create space availability');
            }
        }
    }

    public function actionConfirmAttendance($email, $appointmentId)
    {
        $email = Yii::$app->request->post('email');
        $appointmentId = Yii::$app->request->post('appointmentId');

        if (!$email || !$appointmentId) {
            return $this->errorResponse(['message' => ['Invalid data submitted.']]);
        }
        $decodedEmail = base64_decode($email);

        $attendee = AppointmentAttendees::findOne([
            'email' => $decodedEmail,
            'appointment_id' => $appointmentId
        ]);

        if (!$attendee) {
            return $this->errorResponse(['message' => ['Invalid confirmation link.']]);
        }

        $attendee->status = AppointmentAttendees::STATUS_CONFIRMED;
        if ($attendee->save()) {
            return $this->toastResponse([
                'statusCode' => 200,
                'message' => 'Your attendance has been confirmed. Thank you!',
            ]);
        } else {
            return $this->errorResponse(['message' => ['Unable to confirm your attendance. Please try again later.']]);
        }
    }
}
