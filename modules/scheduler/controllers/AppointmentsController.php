<?php

namespace scheduler\controllers;

use Yii;
use scheduler\models\Appointments;
use scheduler\models\searches\AppointmentsSearch;
use scheduler\models\Availability;
use scheduler\hooks\TimeHelper;
use scheduler\hooks\AppointmentRescheduler as Ar;
/**
 * @OA\Tag(
 *     name="Appointments",
 *     description="Available endpoints for Appointments model"
 * )
 */
class AppointmentsController extends \helpers\ApiController{

    // private $isSelfBooking = false;

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
        $searchModel = new AppointmentsSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams,'AppointmentsSearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider,['oneRecord'=>false]);
    }

    public function actionView($id)
    {
        Yii::$app->user->can('schedulerAppointmentsView');
        return $this->payloadResponse($this->findModel($id));
    }

    public function actionCreate($dataRequest = null)
    {
        // Yii::$app->user->can('schedulerAppointmentsCreate');
        $model = new Appointments();
        $model->loadDefaultValues();
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();

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
            $dataRequest['Appointments']['end_time']
        );

        if ($appoitmentExists) {
            return $this->payloadResponse(['message' => 'The requested time slot is already booked.',]);
        }

        if($model->load($dataRequest) && $model->save()) {
                return $this->payloadResponse($model,['statusCode'=>201,'message'=>'Appointment added successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionUpdate($id)
    {
        // Yii::$app->user->can('schedulerAppointmentsUpdate');
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();
        $model = $this->findModel($id);
        if($model->load($dataRequest) && $model->save()) {
           return $this->payloadResponse($this->findModel($id),['statusCode'=>202,'message'=>'Appointments updated successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->is_deleted) {
            // Yii::$app->user->can('schedulerAppointmentsRestore');
            $model->restore();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Appointments restored successfully']);
        } else {
            Yii::$app->user->can('schedulerAppointmentsDelete');
            $model->delete();
            return $this->toastResponse(['statusCode'=>202,'message'=>'Appointments deleted successfully']);
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

    public function actionSelfBook()
    {
        // Ensure the user is logged in (i.e., is the VC)
        // if (Yii::$app->user->isGuest) {
        //     return $this->errorResponse(['message' => 'You must be logged in to book an appointment.']);
        // }
        
        $model = new Appointments();
        $model->loadDefaultValues();
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();

        // $userId = Yii::$app->user->identity->getId();

        //$dataRequest['Appointments']['user_id'] = $userId
        $user_id = $dataRequest['Appointments']['user_id'];
        $start_date = $dataRequest['Appointments']['appointment_date'];
        $end_date = $dataRequest['Appointments']['appointment_date'];
        $start_time = $dataRequest['Appointments']['start_time'];
        $end_time = $dataRequest['Appointments']['end_time'];

        $dataRequest['Appointments']['email_address'] = 'adminvc@gmail.com';


        // trigger rescheduling logic 
        $appointments = Ar::rescheduleAffectedAppointments(
                $user_id, 
                $start_date, 
                $end_date, 
                $start_time, 
                $end_time
        );

        // return $appointments;

        $model->status = 'self';

        if($model->load($dataRequest) && $model->save()) {
                $data = [
                    $model,
                    'affectedAppointments' => $appointments
                ];
                return $this->payloadResponse(
                    $data, ['statusCode' => 201, 'message' => 'self-booking completed successfully']
               );
        }
        return $this->errorResponse($model->getErrors());
         
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

    public function actionGetSlots()
    {
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();
        $user_id = $dataRequest['Appointments']['user_id'];
        $date = $dataRequest['Appointments']['date'];

        if(empty($user_id) || empty($date)){
            return $this->errorResponse(['message' => ['user id and date are required']]);
        }

        $slots = TimeHelper::getAvailableSlots($user_id, $date);
        return $this->payloadResponse(['slots' => $slots]);
    }

    public function actionSuggestAvailableSlots()
    {
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();
        $rescheduledAppointmentId = $dataRequest['Appointments']['id'];

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
}
