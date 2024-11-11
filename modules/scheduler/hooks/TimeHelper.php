<?php

namespace scheduler\hooks;

use scheduler\models\Settings;
use scheduler\models\Appointments;
use scheduler\models\Availability;
use yii\helpers\ArrayHelper;



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
     * Get the number of days between 2 dates.
     *
     * @param DateTime $date1
     * @param DateTime $date2
     * @return string
     */
    public static function getDifferenceBetweenDates($date1, $date2){
        
        $days = date_diff($date1, $date2)->format("%R%a days");
        return $days;
    }

     /**
     * Get information about the date.
     *
     * @param DateTime $date
     * @return array
     */
    public static function getDateInfo($date)
    {
        $info = date_parse($date);
        foreach ($info as $key => $value) {
            return [
                $key => $value
            ];
        }
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
     * Get available slots for a specific date.
     *
     * @param int $user_id - The user ID (e.g., Vice Chancellor)
     * @param string $date - The date (e.g., '2024-08-27')
     * @param string $startTime - The start time (e.g., '08:00')
     * @param string $endTime - The end time (e.g., '17:00')
     * @param int $interval - The slot duration in minutes (e.g., 30)
     * @return array - List of available time slots
     */
    public static function getAvailableSlots($user_id, $date, $priority = 1)
    {
        $allSlots = self::generateTimeSlots($user_id);
        // return $allSlots;
        if(!is_array($allSlots)){
            return 'No available slots';
        }

        $slotsWithAvailability = [];

        foreach ($allSlots as $slot) {
            $isAvailable = self::isSlotAvailable($user_id, $date, $slot);
            // return $isAvailable;
            list($slotStart, $slotEnd) = explode('-', $slot);
            $expireTime = self::checkExpireTime($date, $slotStart);
            $slotDetails = [
                    'startTime' => $slotStart,
                    'endTime' => $slotEnd,
                    'booked' => !$isAvailable,
                    'is_expired' => $expireTime,
                    // 'can_overide' => self::checkOveride($user_id, $date, $slot, $priority)
            ];

            // if ($priority !== null) {
            //     $slotDetails['can_override'] = self::checkOveride($user_id, $date, $slot, $priority);
            // }

            $slotsWithAvailability[] = $slotDetails;
        }

        return $slotsWithAvailability;
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

        if(empty($startTime) || empty($endTime)){
            return 'start time or endtime is not set';
        }

        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);
        $interval = Settings::find()->select('slot_duration')->where(['user_id' => $user_id])->scalar();

        if(empty($interval)){
            return 'slot duration is not set';
        }

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
    public static function isSlotAvailable($user_id, $date, $slot)
    {
        [$start_time, $end_time] = explode('-', $slot);

        // Check in  slots availability
        $unavailable = Availability::isUnavailableSlot($user_id, $date, $start_time, $end_time);

        if ($unavailable) {
            return false;
        }
        
        // Check in the appointments table
        $appointmentOverlap = Appointments::hasOverlappingAppointment($user_id, $date, $start_time, $end_time);

        return !$appointmentOverlap;
    }

    /**
     * Validate if a booking duration is valid based on the interval.
     *
     * @param string $startTime - The start time (e.g., '10:00')
     * @param string $endTime - The end time (e.g., '11:30')
     * @param int $interval - The slot duration in minutes (e.g., 30)
     * @return bool - True if valid, false otherwise
     */
    public static function isValidBookingDuration($user_id, $startTime, $endTime, $interval = 30)
    {
        $interval= Settings::find()
                        ->select('slot_duration')
                        ->where(['user_id' => $user_id])
                        ->scalar();

        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);
        $difference = $start->diff($end);
        $minutes = ($difference->h * 60) + $difference->i;

        return ($minutes % $interval) === 0;
    }

    /**
     * Validate if the appointment is within the open booking window.
     *
     * @return bool - True if valid, false otherwise
     */
    public static function isWithinBookingWindow($user_id, $appointmentDate)
    {
        $bookingWindow = Settings::find()
                         ->select('booking_window')
                         ->where(['user_id' => $user_id])
                         ->scalar();

        $bookingWindowMonths = $bookingWindow; //* 30; // 30 is number of days in a months

        // maximum allowable appointment date
        $currentDate = new \DateTime();

        $maxBookingDate = clone $currentDate;

        $maxBookingDate->modify("+$bookingWindowMonths months");

        $appointmentDate = new \DateTime($appointmentDate);

        //check if the appointment is within booking window
        if($appointmentDate <= $maxBookingDate){
            return true;
        }
        return false;
    }

    /**
     * Validate if the appointment is in advance of the minimun booking time.
     *
     * @return bool - True if booking time is within the advance or minimum booking time, false otherwise
     */
    public static function validateAdvanceBooking($user_id, $appointmentTime, $date)
    {
        $advancedBookingTime = (int)Settings::getAdvanceBookingDuration($user_id);

        if (!$advancedBookingTime) {
            $advancedBookingTime = 30; // default to 30 minutes
        }

        $currentDateTime = time();
        $appointmentDateTime = strtotime($date . ' ' . $appointmentTime);

        $minimumAdvanceTime = strtotime('+' . $advancedBookingTime . ' minutes', $currentDateTime);

        if ($date == date('Y-m-d') && $appointmentDateTime < $minimumAdvanceTime) {
            return false;
        }

        return true;
    }



    public static function checkExpireTime($appointment_date, $slot_start_time)
    {
        // Get current date and time
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');

        // Check if the appointment date is today
        if ($appointment_date == $currentDate) {
            // If the slot's start time is earlier than the current time, it's expired
            return $slot_start_time < $currentTime;
        }

        // For future dates, the slot is not expired
        return false;
    }

   public static function checkOveride($user_id, $date, $slot, $priority = 3)
   {
        // If there's an overlapping appointment, check if it can be overridden
        if ($appointment) {
            return Appointments::canOverride($appointment, $priority);
        }

        // If no appointment overlaps, no override is necessary
        return false;
    }

}