<?php

namespace helpers\traits;

use  Yii;
use scheduler\models\Events;
use scheduler\models\SpaceAvailability;
use scheduler\models\AppointmentAttendees;
use scheduler\hooks\TimeHelper;

trait AppointmentPolicy
{
	public function checkOverlappingEvents($dataRequest)
	{
		$hasOverlappingEvents = Events::getOverlappingEvents(
            $dataRequest['Appointments']['appointment_date'],
            $dataRequest['Appointments']['start_time'],
            $dataRequest['Appointments']['end_time']
        );

        if ($hasOverlappingEvents) {
            return $this->payloadResponse(['message' => 'The requested appointment time conflicts with an existing event.']);
        }

        return true; // If validation passes
	}

	public function checkOverlappingSpace($dataRequest)
	{
		$hasOverlappingSpace = SpaceAvailability::getOverlappingSpace(
            $dataRequest['Appointments']['space_id'],
            $dataRequest['Appointments']['appointment_date'],
            $dataRequest['Appointments']['start_time'],
            $dataRequest['Appointments']['end_time']
        );

        if ($hasOverlappingSpace) {
            return $this->payloadResponse(['message' => 'The selected time slot for the space is already occupied.']);
        }

        return true; // If validation passes
	}

	public function checkAdvanceBooking($dataRequest)
	{
		$advanced = TimeHelper::validateAdvanceBooking(
            $dataRequest['Appointments']['user_id'],
            $dataRequest['Appointments']['start_time'],
            $dataRequest['Appointments']['appointment_date']
        );

        if ($advanced) {
            return $this->payloadResponse(['message' => 'You cannot book an appointment this soon. Please choose a later time.']);
        }

        return true; // If validation passes
	}

	public function checkBookingWindow($dataRequest)
	{
		$validateBookingWindow = TimeHelper::isWithinBookingWindow(
            $dataRequest['Appointments']['user_id'],
            $dataRequest['Appointments']['appointment_date']
        );

        if (!$validateBookingWindow) {
            return $this->payloadResponse(['message' => 'Appointment is not within the open booking period']);
        }

        return true; // If validation passes
	}

	public function confirmAvailability($dataRequest)
	{
		$isAvailable = $this->checkAvailability(
                $dataRequest['Appointments']['user_id'], 
                $dataRequest['Appointments']['appointment_date'], 
                $dataRequest['Appointments']['start_time'],
                $dataRequest['Appointments']['end_time']
        );

        if (!$isAvailable) {
            return $this->payloadResponse(['message' => 'The requested time slot is blocked.',]);
        }

        return true; // If validation passes
	}

	public function checkOverlappingAppoiment($dataRequest, $model) 
	{
		$appoitmentExists = $model::hasOverlappingAppointment(
            $dataRequest['Appointments']['user_id'], 
            $dataRequest['Appointments']['appointment_date'], 
            $dataRequest['Appointments']['start_time'],
            $dataRequest['Appointments']['end_time'],
            $dataRequest['Appointments']['priority']
        );

        if ($appoitmentExists) {
            return $this->payloadResponse(['message' => 'The requested time slot is already booked.',]);
        }

        return true; // If validation passes
	}

	public function checkAttendeesAvailability($dataRequest)
	{
	    $attendees = $dataRequest['Appointments']['attendees'] ?? [];

	    if (!empty($attendees)) {
	        foreach ($attendees as $attendeeId) {
	            $appointmentConflict = AppointmentAttendees::isAttendeeUnavailable(
	                $attendeeId, 
	                $dataRequest['Appointments']['appointment_date'], 
	                $dataRequest['Appointments']['start_time'],
	                $dataRequest['Appointments']['end_time']
	            );

	            if ($appointmentConflict) {
	                return $this->payloadResponse(['message' => 'One or more attendees are already booked for this time slot.']);
	            }
	        }
	    }

	    // Return true if there are no conflicts or no attendees to check
	    return true;
	}

}