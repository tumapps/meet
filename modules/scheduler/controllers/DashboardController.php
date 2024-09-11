<?php

namespace scheduler\controllers;

use Yii;
use scheduler\models\Appointments;
use scheduler\models\searches\AppointmentsSearch;


class DashboardController extends \helpers\ApiController 
{
	public function actionIndex(){
		return 'ok';
	}

	public function actionMenus(){

		$menus = [
			['route' => 'appointments', 'label' => 'Appointments'],
	        ['route' => 'dashboard', 'label' => 'Dashboard'],
	        ['route' => 'users', 'label' => 'Users'],
		];

		return $this->payloadResponse($menus);
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