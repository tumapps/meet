<?php

namespace scheduler\controllers;

use Yii;
use scheduler\models\Appointments;
use Ratchet\Client\Connector;
use React\EventLoop\Factory;
 


class DashboardController extends \helpers\ApiController 
{
	 
	public $menus = [];

	public function actionIndex(){

		$model = new Appointments();
		$dashboardData = [
			'appointments' => [
				'upcoming' => $model->upComingAppointments(),
				'active' => $model->getActiveAppointments(),
				'all' => $model->getAllAppointments(),
				'canceled' => $model->getCancelledAppointments(),
				'reschedule' => $model->getRescheduleAppointments(),
				'rescheduled' => $model->getRescheduledAppointments(),
			],
		];

		// $this->broadcastAppointmentUpdate($dashboardData);

		return $this->payloadResponse($dashboardData);
	}

	 
	public function broadcastAppointmentUpdate($data)
	{
	    
	    $dataToSend = json_encode($data);

	    // Broadcast using WebSocket
	    $loop = Factory::create();
	    $connector = new Connector($loop);
	    
	    $connector('ws://localhost:8080')
	        ->then(function($conn) use ($dataToSend) {
	            $conn->send($dataToSend); // Send the data to WebSocket server
	            $conn->close();
	        }, function($e) {
	            echo "Could not connect to WebSocket: {$e->getMessage()}\n";
	        });

	    $loop->run();
	}

	public function actionMenus(){

		$this->menus = Yii::$app->params['menus'];
			 
		return $this->payloadResponse($this->menus);
	}

	public function actionUpcomingAppoitments()
	{
		$model = new Appointments();
		if(empty($model->upComingAppointments())){
			return $this->payloadResponse(['message' => 'No upcoming Appointments']);
		}
		return $this->payloadResponse(['appointments' => $model->upComingAppointments()]);
	}

	public function actionActiveAppointments(){
		$model = new Appointments();
		return $this->payloadResponse(['appointments' => $model->getActiveAppointments()]);
	}

	public function actionAllAppointments(){
		$model = new Appointments();
		return $this->payloadResponse(['appointments' => $model->getAllAppointments()]);
	}

	public function actionRescheduleAppointments(){
		$model = new Appointments();
		return $this->payloadResponse(['appointments' => $model->getRescheduleAppointments()]);
	}

	public function actionRescheduledAppointments(){
		$model = new Appointments();
		return $this->payloadResponse(['appointments' => $model->getRescheduledAppointments()]);
	}

	public function actionCancelledAppointments(){
		$model = new Appointments();
		return $this->payloadResponse(['appointments' => $model->getCancelledAppointments()]);
	}

}



