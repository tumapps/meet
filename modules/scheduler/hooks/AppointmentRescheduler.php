<?php 

namespace scheduler\hooks;

use scheduler\models\Appointments;
use scheduler\models\Settings;
use scheduler\models\Availability;
use scheduler\hooks\TimeHelper;

class AppointmentRescheduler {

	public static function rescheduleAffectedAppointments($user_id, $start_date, $end_date, $start_time, $end_time)
	{
        $affectedAppointments = self::getAffectedAppointments(
        	$user_id, $start_date, $end_date, $start_time, $end_time
        );

        foreach ($affectedAppointments as $appointment) {
            self::rescheduleAppointment($appointment);
        }

        return $affectedAppointments;

        self::sendNotifications($affectedAppointments);
	}

	private static function getAffectedAppointments($user_id, $start_date, $end_date, $start_time, $end_time)
    {

	      $appointment = new Appointments();

	      return $appointment->getOverlappingAppointment(
	      	$user_id, $start_date, $end_date, $start_time, $end_time
	      );
    }

    protected static function rescheduleAppointment($appointment)
    {
        $appointment->status = Appointments::STATUS_RESCHEDULED;
        $appointment->save();
    }

	public static function findNextAvailableSlot($user_id, $appointment_date, $startTime, $endTime)
	{
	    // Get slot duration for a given time range
	    $slotDuration = TimeHelper::calculateDuration(new \DateTime($startTime), new \DateTime($endTime));

	    $date = $appointment_date;

	    while (true) {
	        // Get all booked slots for the given date (i.e., those that can't be booked in the appointments table)
	        $bookedSlots = Appointments::getBookedSlotsForRange($user_id, $date, $date);

	        // Get all the unavailable time range for the given date (i.e., those that can't be booked since the user is unavailable)
	        $unavailableSlots = Availability::getUnavailableSlotDetails($user_id, $date, $startTime, $endTime);

	        // Calculate available slots based on working hours and unavailable slots
	        $availableSlots = self::calculateAvailableSlots($user_id, $bookedSlots, $unavailableSlots, $date, $startTime, $endTime, $slotDuration);

	        // Check if there are available slots for the current day
	        if (!empty($availableSlots)) {
	            // Find slots that fit the duration of the affected appointment
	            $suitableSlots = self::findSlotsThatFitDuration($availableSlots, $slotDuration);

	            if (!empty($suitableSlots)) {
	                return [
	                    'date' => $date,
	                    'slots' => $suitableSlots
	                ];
	            }
	        }

	        // Move to the next day
	        $date = date('Y-m-d', strtotime($date . ' +1 day'));
   	 	}
	}


	protected static function findSlotsThatFitDuration($availableSlots, $slotDuration)
	{
	    $suitableSlots = [];

	    foreach ($availableSlots as $slot) {
	        $slotStart = strtotime($slot['start_time']);
	        $slotEnd = strtotime($slot['end_time']);

	        if (($slotEnd - $slotStart) >= $slotDuration) {
	            $suitableSlots[] = $slot;
	        }
	    }

	    return $suitableSlots;
	}



    private  function sendNotifications($appointments)
    {
        // Implement logic to send notifications to users about the rescheduling
    }
 
	protected static function calculateAvailableSlots($user_id, $bookedSlots, $unavailableSlots, $date, $startTime, $endTime, $slotDuration)
	{
	    // Convert slot duration from minutes to seconds
	    $slotDurationInSeconds = $slotDuration * 60;

	    // Get working hours for the user
	    $workingHours = Settings::getWorkingHours($user_id);
	    $dayStart = strtotime($date . ' ' . $workingHours['start_time']);
	    $dayEnd = strtotime($date . ' ' . $workingHours['end_time']);

	    $availableSlots = [];
	    $previousEnd = $dayStart;

	    // Merge booked and unavailable slots and sort by start time
	    $allSlots = array_merge($bookedSlots, $unavailableSlots);
	    usort($allSlots, function($a, $b) {
	        return strtotime($a['start_time']) - strtotime($b['start_time']);
	    });

	    // Iterate over the slots to find gaps
	    foreach ($allSlots as $slot) {
	        $slotStart = strtotime($slot['start_time']);
	        $slotEnd = strtotime($slot['end_time']);

	        // Check if there's a gap between the previous end time and the start of the next booked/unavailable slot
	        while ($previousEnd + $slotDurationInSeconds <= $slotStart) {
	            $availableSlots[] = [
	                'date' => $date,
	                'start_time' => date('H:i:s', $previousEnd),
	                'end_time' => date('H:i:s', $previousEnd + $slotDurationInSeconds)
	            ];
	            $previousEnd += $slotDurationInSeconds;
	        }

	        // Update the previous end time to the current slot's end time
	        $previousEnd = max($previousEnd, $slotEnd);
	    }

	    // Finally, check the gap between the last slot and the end of the working day
	    while ($previousEnd + $slotDurationInSeconds <= $dayEnd) {
	        $availableSlots[] = [
	            'date' => $date,
	            'start_time' => date('H:i:s', $previousEnd),
	            'end_time' => date('H:i:s', $previousEnd + $slotDurationInSeconds)
	        ];
	        $previousEnd += $slotDurationInSeconds;
	    }

	    return $availableSlots;
	}


}

