<?php

namespace scheduler\hooks;

use scheduler\models\Appointments;
use scheduler\models\Settings;
use scheduler\hooks\TimeHelper;

class AppointmentRescheduler
{

	public static function rescheduleAffectedAppointments($user_id, $start_date, $end_date, $start_time, $end_time)
	{
		$affectedAppointments = self::getAffectedAppointments(
			$user_id,
			$start_date,
			$end_date,
			$start_time,
			$end_time
		);

		foreach ($affectedAppointments as $appointment) {
			self::rescheduleAppointment($appointment);
		}

		self::sendNotifications($affectedAppointments);

		return $affectedAppointments;
	}

	private static function getAffectedAppointments($user_id, $start_date, $end_date, $start_time, $end_time)
	{

		$appointment = new Appointments();

		return $appointment->getOverlappingAppointment(
			$user_id,
			$start_date,
			$end_date,
			$start_time,
			$end_time
		);
	}

	protected static function rescheduleAppointment($appointment)
	{
		$appointment->status = Appointments::STATUS_RESCHEDULE;
		$appointment->save(false);
	}

	public static function findNextAvailableSlot($user_id, $appointment_date, $startTime, $endTime,  $disableWeekends = true)
	{
		$today = date('Y-m-d');

		// Ensure the search starts from today or the provided future date
		if ($appointment_date < $today) {
			$appointment_date = $today;
		}
		// Get slot duration for a given time range
		$slotDuration = TimeHelper::calculateDuration(new \DateTime($startTime), new \DateTime($endTime));

		while (true) {

			if ($disableWeekends) {
				$dayOfWeek = date('N', strtotime($appointment_date));
				if ($dayOfWeek >= 6) {
					$appointment_date = date('Y-m-d', strtotime($appointment_date . ' +1 day'));
					continue;
				}
			}

			// Calculate available slots based on working hours and unavailable slots
			$availableSlots = self::calculateAvailableSlots($user_id, $appointment_date, $startTime, $endTime, $slotDuration);

			// Check if there are available slots for the current day
			if (!empty($availableSlots)) {
				// Find slots that fit the duration of the affected appointment
				$suitableSlots = self::findSlotsThatFitDuration($availableSlots, $slotDuration, $appointment_date);

				if (!empty($suitableSlots)) {
					return [
						'date' => $appointment_date,
						'slots' => $suitableSlots
					];
				}
			}

			// Move to the next day
			$nextDate = date('Y-m-d', strtotime($appointment_date . ' +1 day'));
			return self::findNextAvailableSlot($user_id, $nextDate, $startTime, $endTime);
		}
	}

	protected static function findSlotsThatFitDuration($availableSlots, $requiredSlotDuration, $appointment_date)
	{
		$suitableSlots = [];
		$currentSlotGroup = [];
		$accumulatedDuration = 0;

		foreach ($availableSlots as $slot) {
			// Split the slot into start and end times
			[$slotStart, $slotEnd] = explode('-', $slot);

			if (TimeHelper::checkExpireTime($appointment_date, $slotStart)) {
				continue; // Skip expired slots
			}


			// Calculate the duration of this slot in minutes
			$slotDuration = (strtotime($slotEnd) - strtotime($slotStart)) / 60;

			// Accumulate the duration and add the slot to the current group
			$accumulatedDuration += $slotDuration;
			$currentSlotGroup[] = [
				'startTime' => $slotStart,
				'endTime' => $slotEnd,
				'booked' => false,
				// 'is_expired' => TimeHelper::checkExpireTime($appointment_date, $slotStart)
			];

			// Check if the accumulated duration meets or exceeds the required duration
			if ($accumulatedDuration >= $requiredSlotDuration) {
				// If it meets the required duration, add the current group to the suitable slots
				$suitableSlots = array_merge($suitableSlots, $currentSlotGroup);

				// Reset the current group and accumulated duration for the next batch
				$currentSlotGroup = [];
				$accumulatedDuration = 0;
			}
		}

		return $suitableSlots;
	}

	private static function sendNotifications($appointments)
	{
		$model = new Appointments();

		if (!empty($appointments)) {
			$model->sendAffectedAppointmentsEvent($appointments);

			foreach ($appointments as $appointment) {
				$appointment->sendAppointmentRescheduleEvent();
			}
		}
	}

	protected static function calculateAvailableSlots($user_id, $date, $startTime, $endTime, $slotDuration)
	{
		// Convert slot duration from minutes to seconds
		$slotDurationInSeconds = $slotDuration * 60;

		// Get working hours for the user
		$workingHours = Settings::getWorkingHours($user_id);
		$dayStart = strtotime($date . ' ' . $workingHours['start_time']);
		$dayEnd = strtotime($date . ' ' . $workingHours['end_time']);

		// Initialize array to store all possible time slots for the day
		$availableSlots = [];
		$slots = TimeHelper::generateTimeSlots($user_id);

		foreach ($slots as $slot) {
			if (TimeHelper::isSlotAvailable($user_id, $date, $slot)) {
				$availableSlots[] = $slot;
			}
		}
		return $availableSlots;
	}
}
