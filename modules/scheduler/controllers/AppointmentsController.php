<?php

namespace scheduler\controllers;

use Yii;
use scheduler\models\Appointments;
use scheduler\models\searches\AppointmentsSearch;
use scheduler\models\Availability;
/**
 * @OA\Tag(
 *     name="Appointments",
 *     description="Available endpoints for Appointments model"
 * )
 */
class AppointmentsController extends \helpers\ApiController{
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

    public function actionCreate()
    {
        // Yii::$app->user->can('schedulerAppointmentsCreate');
        $model = new Appointments();
        $model->loadDefaultValues();
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();
        // checking if the slots are available for booking
        $isAvailable = $this->checkAvailability(
            $dataRequest['Appointments']['user_id'], 
            $dataRequest['Appointments']['appointment_date'], 
            $dataRequest['Appointments']['start_time'],
            $dataRequest['Appointments']['end_time']
        );

        if(!$isAvailable){
            return $this->errorResponse([
                    'message' => [
                         'The VC is unavailable for the requested time slot',
                    ],
            ]);
        }

        // cheking if there is overlapping appoiment ie if the appoitment is already placed
        $appoitmentExists = $model::hasOverlappingAppointment(
            $dataRequest['Appointments']['user_id'], 
            $dataRequest['Appointments']['appointment_date'], 
            $dataRequest['Appointments']['start_time'],
            $dataRequest['Appointments']['end_time']
        );

        if ($appoitmentExists) {
            return $this->errorResponse([
                    'message' => [
                         'The requested time slot is already booked.',
                    ],
            ]);
        }

        if($model->load($dataRequest) && $model->save()) {
                return $this->payloadResponse($model,['statusCode'=>201,'message'=>'Appointment added successfully']);
        }
        return $this->errorResponse($model->getErrors()); 
    }

    public function actionUpdate($id)
    {
        Yii::$app->user->can('schedulerAppointmentsUpdate');
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
            Yii::$app->user->can('schedulerAppointmentsRestore');
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

    private function checkAvailability($vc_id, $appointment_date, $start_time, $end_time)
    {
        $bookedSlots = Availability::getUnavailableSlots($vc_id, $appointment_date, $start_time, 
            $end_time);

        $appointmentStart = new \DateTime("$appointment_date $start_time");
        $appointmentEnd = new \DateTime("$appointment_date $end_time");

        foreach ($bookedSlots as $slot) {
            $slotStart = new \DateTime("$appointment_date {$slot->start_time}");
            $slotEnd = new \DateTime("$appointment_date {$slot->end_time}");

            if ($appointmentStart < $slotEnd && $appointmentEnd > $slotStart) {
                return false;
            }
        }

        return true;
    }
}
