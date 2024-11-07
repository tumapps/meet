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
class AppointmentsController extends \helpers\ApiController {

    use AppointmentPolicy;

    public $permissions = [
        'schedulerAppointmentsList'=>'View Appointments List',
        'schedulerAppointmentsCreate'=>'Add Appointments',
        'schedulerAppointmentsUpdate'=>'Edit Appointments',
        'schedulerAppointmentsDelete'=>'Delete Appointments',
        'schedulerAppointmentsRestore'=>'Restore Appointments',
        ];
        
    public function actionIndex()
    {
        // Yii::$app->user->can('schedulerAppointmentsList');
        $currentUserId = Yii::$app->user->id;
        $canBeBooked = Yii::$app->user->identity->can_be_booked;

        $searchModel = new AppointmentsSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams,'AppointmentsSearch');
        

        // $dataProvider = $searchModel->search($search);
        
        // if($canBeBooked){
        //     $dataProvider->query->andWhere(['user_id' => $currentUserId]);
        // }

         // Secretary (cannot be booked) can filter appointments by 'user_id'
        if (!$canBeBooked && isset($search['user_id']) && !empty($search['user_id'])) {
            // Secretary is allowed to filter by user_id
            $dataProvider = $searchModel->search($search);
        } else {
            // Non-secretary users can only see their own appointments
            $dataProvider = $searchModel->search($search);
            if ($canBeBooked) {
                $dataProvider->query->andWhere(['user_id' => $currentUserId]);
            }
        }

        $appointments = $dataProvider->getModels();

        // Add statusLabel to each appointment
        foreach ($appointments as &$appointment) {
            $appointmentData = $appointment->toArray();
            // $appointmentData['statusLabel'] = Appointments::getStatusLabel($appointment->status);
            $appointmentData['userName'] = Appointments::getUserName($appointment->user_id);
            $appointment = $appointmentData;
        }

        $dataProvider->setModels($appointments);

        return $this->payloadResponse($dataProvider,['oneRecord'=>false]);
    }

    public function actionView($id)
    {
        // Yii::$app->user->can('schedulerAppointmentsView');
        $appointment = $this->findModel($id);

        $statusLabel = Appointments::getStatusLabel($appointment->status);
        $appoitmentData = $appointment->toArray();
        $appoitmentData['statusLabel'] = $statusLabel; 

        return $this->payloadResponse($appoitmentData);

        // return $this->payloadResponse($this->findModel($id));
    }

    public function actionAppointmentsTypes()
    {

        $model = new AppointmentType();
        $appointmentTypes = $model->getAppointmentTypes();

        $types = [];
        foreach($appointmentTypes as $appointmentType){
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
            return $this->toastResponse(['statusCode'=>202,'message'=>$checkinResponse['message']]);
        }

        // $appointment = Appointments::findOne($id);
        // $message = $appointment->checked_in ? 'Appointment has been marked as Attended' : 'Appointment has been unchecked';

        return $this->toastResponse(['statusCode'=>202,'message'=>$checkinResponse['message']]);
        // return $this->toastResponse(['statusCode' => 202, 'message' => $message]);
    }

    public function actionCreate($dataRequest = null)
    {
        // Yii::$app->user->can('schedulerAppointmentsCreate');
        $model = new Appointments();
        $model->loadDefaultValues();
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();

        if($model->load($dataRequest)) {
            if(!$model->validate()) {
                return $this->errorResponse($model->getErrors()); 
            }

            if (($overlapResponse = $this->checkOverlappingEvents($dataRequest)) !== true) {
                return $overlapResponse;
            }

            if (($spaceOverlapResponse = $this->checkOverlappingSpace($dataRequest)) !== true) {
                return $spaceOverlapResponse;
            }

            if (($advanceResponse = $this->checkAdvanceBooking($dataRequest)) !== true) {
                return $advanceResponse;
            }

            if (($windowResponse = $this->checkBookingWindow($dataRequest)) !== true) {
                return $windowResponse;
            }

            if (($availabilityResponse = $this->confirmAvailability($dataRequest)) !== true) {
                return $availabilityResponse;
            }

            if (($attendeesResponse = $this->checkAttendeesAvailability($dataRequest)) !== true) {
                return $attendeesResponse;
            }

            // cheking if there is overlapping appoiment ie if the appoitment is already placed
            if (($overlappingAppoiment = $this->checkOverlappingAppoiment($dataRequest, $model)) !== true) {
                return $overlappingAppoiment;
            }

            // get uploaded files ie
            $uploadedFile = UploadedFile::getInstanceByName('file');

            // Check space level
            $space = Space::findOne($dataRequest['Appointments']['space_id']);
            if (!$space) {
                return $this->payloadResponse(['message' => 'The specified space does not exist.']);
            }

            $levelName = $space->level ? $space->level->name : null;

             if ($levelName === 'Level 4') {
                if ($model->save()) {
                    // save attendees
                    $this->saveAttendees($dataRequest, $model->id);
                    $this->handleFileUpload($uploadedFile, $model->id);

                    $model->sendAppointmentCreatedEvent(
                        $dataRequest['Appointments']['email_address'],
                        $dataRequest['Appointments']['contact_name'], 
                        $dataRequest['Appointments']['user_id'],
                        $dataRequest['Appointments']['appointment_date'], 
                        $dataRequest['Appointments']['start_time'],
                        $dataRequest['Appointments']['end_time']
                    );
                    return $this->payloadResponse($model, ['statusCode' => 201, 'message' => 'Appointment added successfully']);
                }
             } else {
                $model->status = Appointments::STATUS_PENDING;
                if ($model->save()) {
                    // save attendees
                    $this->saveAttendees($dataRequest, $model->id);
                    $this->handleFileUpload($uploadedFile, $model->id);
                    return $this->payloadResponse($model, ['statusCode' => 201, 'message' => 'Appointment created successfully, Pending Approval']);
                }
             }
        }
    }

    public function actionUpdate($id)
    {
        // Yii::$app->user->can('schedulerAppointmentsUpdate');
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);

        if($model->load($dataRequest)) {
            if(!$model->validate()) {
                return $this->errorResponse($model->getErrors()); 
            }
        
            $advanced = TimeHelper::validateAdvanceBooking(
                 $dataRequest['Appointments']['user_id'],$dataRequest['Appointments']['start_time'],
                 $dataRequest['Appointments']['appointment_date']
            );

            if($advanced) {
                return $this->payloadResponse(['message' => 'The selected appoitment start time overlaps with the minimum advanced booking time']); 
            }

            $validateBookingWindow = TimeHelper::isWithinBookingWindow(
                $dataRequest['Appointments']['user_id'],$dataRequest['Appointments']['appointment_date']
            );

            if(!$validateBookingWindow){
                return $this->payloadResponse(['message' => 'Appoitment is not within the open booking period',]); 
            }

            $isAvailable = $this->checkAvailability(
                $dataRequest['Appointments']['user_id'], 
                $dataRequest['Appointments']['appointment_date'], 
                $dataRequest['Appointments']['start_time'],
                $dataRequest['Appointments']['end_time']
            );

            if (!$isAvailable) {
                return $this->payloadResponse(['message' => 'The requested time slot is blocked.',]);
            }
            
            // cheking if there is overlapping appoiment ie if the appoitment is already placed
            $appoitmentExists = $model::hasOverlappingAppointment(
                $dataRequest['Appointments']['user_id'], 
                $dataRequest['Appointments']['appointment_date'], 
                $dataRequest['Appointments']['start_time'],
                $dataRequest['Appointments']['end_time'],
                $id
            );

            if ($appoitmentExists) {
                return $this->payloadResponse(['message' => 'The requested time slot is already booked.',]);
            }

            if($model->status === Appointments::STATUS_RESCHEDULE){
                $model->status = Appointments::STATUS_RESCHEDULED;
            }
            
            if($model->save()) {
                if($model->status === Appointments::STATUS_RESCHEDULED){
                    $model->sendAppointmentRescheduledEvent(
                        $model->email_address, $model->appointment_date, $model->start_time, $model->end_time, 
                        $model->contact_name
                    );
                }

                return $this->payloadResponse($this->findModel($id),['statusCode'=>202,'message'=>'Appointments updated successfully']);
            }
        } 
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            // Yii::$app->user->can('schedulerAppointmentsRestore');
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
            return $this->toastResponse(['statusCode'=>202,'message'=>'Appointments restored successfully']);
        } else {
            // Yii::$app->user->can('schedulerAppointmentsDelete');
            $model->delete();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Appointments deleted successfully']);
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

        // Capture who canceled the appointment (VC or Secretary)
        $currentUser = Yii::$app->user->identity;
        $cancelledBy = $currentUser->username;
        $cancelledByRole = $currentUser->can_be_booked ? 'VC/DVC' : 'Secretary';

        if($model->save(false)){

            $model->sendAppointmentCancelledEvent($contact_email, $contact_name, $date, $starTime, $endTime, $bookedUserEmail);
            return $this->toastResponse(['statusCode'=>202,'message'=>'Appointments CANCELLED successfully']);
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
        $bookedSlots = Availability::getUnavailableSlots($user_id, $appointment_date, $start_time, 
            $end_time);

        if($bookedSlots){
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


        if(empty($user_id) || empty($date)){
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


        if(empty($rescheduledAppointmentId)){
            return $this->errorResponse(['message' => ['Appointment ID is required']]);
        }

        $model = new Appointments();
        $appoitment = $model->getRescheduledAppointment($rescheduledAppointmentId);

        if(!$appoitment){
            $msg = 'Appointment with id {' . $rescheduledAppointmentId . '} not found';
            return $this->payloadResponse(['message' => $msg ]);
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

         if(empty($space_id) || empty($date)){
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
                return $this->errorResponse(['message' => 'File upload failed', 'errors' => $uploadResult]);
            }
        }
    }

    protected function saveAttendees($dataRequest, $id) {
        $attendees = $dataRequest['Appointments']['attendees'] ?? [];
        $date = $dataRequest['Appointments']['appointment_date'];
        $startTime = $dataRequest['Appointments']['start_time'];
        $endTime =$dataRequest['Appointments']['end_time'];

        $addAttendees = new AppointmentAttendees();

        if (!empty($attendees)) {
            foreach ($attendees as $attendeeId) {
                $staffId = $attendeeId; 
                $addAttendees->save($id, $staffId, $date, $starTime, $endTime);
            }
        }
    }
}
