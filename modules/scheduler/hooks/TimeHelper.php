<?php

namespace scheduler\hooks;

use scheduler\models\Settings;
use scheduler\models\Appointments;
use scheduler\models\Availability;



class TimeHelper
{
	/**
     * Convert a time string to DateTime object.
     *
     * @param string $time
     * @return DateTime
     */
    public static function convertToDateTime(string $time)
    {
        return new \DateTime($time);
    }

     /**
     * Validate that the end time is not earlier than the start time.
     *
     * @param DateTime $startTime
     * @param DateTime $endTime
     * @return bool
     */
    public static function validateTimeRange(DateTime $startTime, DateTime $endTime)
    {
        return $endTime > $startTime;
    }

    /**
     * Calculate the duration between two DateTime objects in minutes.
     *
     * @param DateTime $startTime
     * @param DateTime $endTime
     * @return int
     */
    public static function calculateDuration($startTime, $endTime)
    {
        $interval = $startTime->diff($endTime);
        return ($interval->h * 60) + $interval->i;
    }

    /**
     * Generate time slots within a specified range.
     *
     * @param string $startTime - The start time (e.g., '08:00')
     * @param string $endTime - The end time (e.g., '17:00')
     * @param int $interval - The slot duration in minutes (e.g., 30)
     * @return array - List of time slots ['08:00-08:30', '08:30-09:00', ...]
     */
    public static function generateTimeSlots($user_id)
    {
        $slots = [];
        $startTime = Settings::find()->select('start_time')->where(['user_id' => $user_id])->scalar();
        $endTime = Settings::find()->select('end_time')->where(['user_id' => $user_id])->scalar();
        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);
        $interval = Settings::find()->select('slot_duration')->where(['user_id' => $user_id])->scalar();

        while ($start < $end) {
            $slotStart = $start->format('H:i');
            $start->modify("+$interval minutes");
            $slotEnd = $start->format('H:i');
            if ($start <= $end) {
                $slots[] = "$slotStart-$slotEnd";
            }
        }

        return $slots;
    }

     /**
     * Check if a specific slot is available on a given date.
     *
     * @param int $user_id - The user ID (e.g.,)
     * @param string $date - The date (e.g., '2024-08-19')
     * @param string $slot - The time slot (e.g., '08:00-08:30')
     * @return bool - True if available, false otherwise
     */
    public static function isSlotAvailable($vc_id, $date, $slot)
    {
        [$start_time, $end_time] = explode('-', $slot);

        // Check in the unavailable slots table
        $unavailable = Availability::isUnavailableSlot($vc_id, $date, $start_time, $end_time);

        if ($unavailable) {
            return false;
        }

        // Check in the appointments table
        $appointmentOverlap = Appointments::hasOverlappingAppointment($vc_id, $date, $start_time, $end_time);

        return !$appointmentOverlap;
    }

    /**
     * Get available slots for a specific date.
     *
     * @param int $vc_id - The user ID (e.g., Vice Chancellor)
     * @param string $date - The date (e.g., '2024-08-19')
     * @param string $startTime - The start time (e.g., '08:00')
     * @param string $endTime - The end time (e.g., '17:00')
     * @param int $interval - The slot duration in minutes (e.g., 30)
     * @return array - List of available time slots
     */
    public static function getAvailableSlots($vc_id, $date)
    {
        $allSlots = self::generateTimeSlots($vc_id);
        $availableSlots = [];

        foreach ($allSlots as $slot) {
            if (self::isSlotAvailable($vc_id, $date, $slot)) {
                $availableSlots[] = $slot;
            }
        }

        return $availableSlots;
    }

    /**
     * Validate if a booking duration is valid based on the interval.
     *
     * @param string $startTime - The start time (e.g., '10:00')
     * @param string $endTime - The end time (e.g., '11:30')
     * @param int $interval - The slot duration in minutes (e.g., 30)
     * @return bool - True if valid, false otherwise
     */
    public static function isValidBookingDuration($startTime, $endTime, $interval = 30)
    {
        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);
        $difference = $start->diff($end);
        $minutes = ($difference->h * 60) + $difference->i;

        return ($minutes % $interval) === 0;
    }

}
