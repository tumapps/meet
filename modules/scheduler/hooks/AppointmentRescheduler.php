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

        return $affectedAppointments;

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
        $appointment->status = $appointment->STATUS_RESCHEDULED;
        $appointment->save();
    }

    public static function findNextAvailableSlot($user_id, $appointment_date, $startTime, $endTime)
    {

    	// get slot duration
    	$slotDuration = TimeHelper::calculateDuration(new \DateTime($startTime), new \DateTime($endTime));

    	$date = $appointment_date;
    	while(true){
    		//retrieve unavailable slots for the given date ie that cant be booked 
    		$unavailableSlots = Appointments::getUnavailableSlotsForRange($user_id, $date, $date);

    		// Calculate available slots based on working hours and unavailable slots
    		$availableSlots = self::calculateAvailableSlots($user_id, $unavailableSlots, $date, $startTime, $endTime);

    		return $availableSlots;

    		// Check if there's an available slot that fits the duration
       	    $slot = self::findSlotThatFitsDuration($availableSlots, $slotDuration);

       	    if ($slot !== null) {
	            return [
	                'date' => $date,
	                'start_time' => $slot['start_time'],
	                'end_time' => $slot['end_time']
	            ];
        	}

    		// Move to the next day if no suitable slot is found
        	$date = date('Y-m-d', strtotime($date . ' +1 day'));
    	}
    	
    	
    }

    protected static function findSlotThatFitsDuration(array $availableSlots, $duration)
	{
	    foreach ($availableSlots as $slot) {
	        $slotStartTime = strtotime($slot['start_time']);
	        $slotEndTime = strtotime($slot['end_time']);
	        $availableDuration = $slotEndTime - $slotStartTime;

	        if ($availableDuration >= $duration) {
	            return [
	                'start_time' => date('H:i:s', $slotStartTime),
	                'end_time' => date('H:i:s', $slotStartTime + $duration)
	            ];
	        }
	    }

	    return null; // No suitable slot found
	}

    private  function sendNotifications($appointments)
    {
        // Implement logic to send notifications to users about the rescheduling
    }

	 
	protected static function calculateAvailableSlots($user_id, $unavailableSlots, $date, $startTime, $endTime)
	{
	    $workingHours = Settings::getWorkingHours($user_id);
	    $dayStart = strtotime($date . ' ' . $workingHours['start_time']);
	    $dayEnd = strtotime($date . ' ' . $workingHours['end_time']);
	    $availableSlots = [];
	    $previousEnd = $dayStart;

	    usort($unavailableSlots, function($a, $b) {
	        return strtotime($a['start_time']) - strtotime($b['start_time']);
	    });

	    foreach ($unavailableSlots as $slot) {
	        $unavailableStart = strtotime($date . ' ' . $slot['start_time']);
	        $unavailableEnd = strtotime($date . ' ' . $slot['end_time']);

	        if ($unavailableStart < $dayEnd && $unavailableEnd > $dayStart) {
	            $unavailableStart = max($unavailableStart, $dayStart);
	            $unavailableEnd = min($unavailableEnd, $dayEnd);

	            if ($unavailableStart > $previousEnd) {
	                $availableSlots[] = [
	                    'start_time' => date('H:i:s', $previousEnd),
	                    'end_time' => date('H:i:s', $unavailableStart)
	                ];
	            }

	            $previousEnd = max($previousEnd, $unavailableEnd);
	        }
	    }

	    if ($previousEnd < $dayEnd) {
	        $availableSlots[] = [
	            'start_time' => date('H:i:s', $previousEnd),
	            'end_time' => date('H:i:s', $dayEnd)
	        ];
	    }

	    $splitSlots = [];
	    foreach ($availableSlots as $slot) {
	        $start = strtotime($date . ' ' . $slot['start_time']);
	        $end = strtotime($date . ' ' . $slot['end_time']);

	        if ($start < strtotime($date . ' 10:00:00') && $end > strtotime($date . ' 08:00:00')) {
	            $splitSlots[] = [
	                'start_time' => date('H:i:s', max($start, strtotime($date . ' 08:00:00'))),
	                'end_time' => date('H:i:s', min($end, strtotime($date . ' 10:00:00')))
	            ];
	        }

	        if ($start < strtotime($date . ' 15:00:00') && $end > strtotime($date . ' 12:30:00')) {
	            $splitSlots[] = [
	                'start_time' => date('H:i:s', max($start, strtotime($date . ' 12:30:00'))),
	                'end_time' => date('H:i:s', min($end, strtotime($date . ' 15:00:00')))
	            ];
	        }

	        if ($start < strtotime($date . ' 17:00:00') && $end > strtotime($date . ' 16:30:00')) {
	            $splitSlots[] = [
	                'start_time' => date('H:i:s', max($start, strtotime($date . ' 16:30:00'))),
	                'end_time' => date('H:i:s', min($end, strtotime($date . ' 17:00:00')))
	            ];
	        }
	    }

	    return $splitSlots;
	}

	protected static function calculateAvailableSlots3($user_id, $unavailableSlots, $date, $startTime, $endTime)
	{
	    // Fetch working hours for the given user
	    $workingHours = Settings::getWorkingHours($user_id);
	    
	    // Convert working hours to timestamps
	    $dayStart = strtotime($date . ' ' . $workingHours['start_time']); 
	    $dayEnd = strtotime($date . ' ' . $workingHours['end_time']);
	    
	    $availableSlots = [];
	    
	    // Initialize previous end time to the start of the working day
	    $previousEnd = $dayStart;

	    // Sort unavailable slots by start time to ensure they are in order
	    usort($unavailableSlots, function($a, $b) {
	        return strtotime($a['start_time']) - strtotime($b['start_time']);
	    });

	    // Iterate through unavailable slots and calculate available slots
	    foreach ($unavailableSlots as $slot) {
	        // Convert unavailable slot times to timestamps
	        $unavailableStart = strtotime($date . ' ' . $slot['start_time']);
	        $unavailableEnd = strtotime($date . ' ' . $slot['end_time']);

	        // Ensure that the slot is within the working hours
	        if ($unavailableStart < $dayEnd && $unavailableEnd > $dayStart) {
	            // Adjust start and end to be within the working hours
	            $unavailableStart = max($unavailableStart, $dayStart);
	            $unavailableEnd = min($unavailableEnd, $dayEnd);

	            // Calculate available slot before the unavailable slot
	            if ($unavailableStart > $previousEnd) {
	                $availableSlots[] = [
	                    'start_time' => date('H:i:s', $previousEnd),
	                    'end_time' => date('H:i:s', $unavailableStart)
	                ];
	            }

	            // Update previous end to the end of the current unavailable slot
	            $previousEnd = max($previousEnd, $unavailableEnd);
	        }
	    }

	    // Add the last available slot if there's a gap at the end of the working hours
	    if ($previousEnd < $dayEnd) {
	        $availableSlots[] = [
	            'start_time' => date('H:i:s', $previousEnd),
	            'end_time' => date('H:i:s', $dayEnd)
	        ];
	    }

	    return $availableSlots;
	}



	
}

