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
    public static function validateTimeRange(\DateTime $startTime, \DateTime $endTime)
    {
        return $endTime > $startTime;
    }


    public static function getDifferenceBetweenDates($date1, $date2)
    {
        $datetime1 = new \DateTime($date1);
        $datetime2 = new \DateTime($date2);
        $interval = $datetime1->diff($datetime2);

        return sprintf(
            "%d years, %d months, %d days",
            $interval->y,
            $interval->m,
            $interval->d
        );
    }


    /**
     * Get information about the date.
     *
     * @param DateTime $date
     * @return array
     */
    public static function getDateInfo($date)
    {
        if ($date instanceof \DateTimeInterface) {
            $date = $date->format('Y-m-d H:i:s');
        }

        $info = date_parse($date);
        return $info;
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
    public static function getAvailableSlots($user_id, $date)
    {
        $allSlots = self::generateTimeSlots($user_id);
        $bufferTime = Settings::find()->select('advanced_booking')->where(['user_id' => $user_id])->scalar() ?? 30; // defaults to 30  mins

        if (!is_array($allSlots)) {
            return 'No available slots';
        }

        $slotsWithAvailability = [];

        foreach ($allSlots as $slot) {
            $isAvailable = self::isSlotAvailable($user_id, $date, $slot);
            list($slotStart, $slotEnd) = explode('-', $slot);
            $expireTime = self::checkExpireTime($date, $slotStart, $bufferTime);
            $slotDetails = [
                'startTime' => $slotStart,
                'endTime' => $slotEnd,
                'booked' => !$isAvailable,
                'is_expired' => $expireTime,
            ];

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

        if (empty($startTime) || empty($endTime)) {
            return 'start time or endtime is not set';
        }

        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);
        $interval = Settings::find()->select('slot_duration')->where(['user_id' => $user_id])->scalar();

        if (empty($interval)) {
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
        $interval = Settings::find()
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

        $bookingWindowMonths = is_numeric($bookingWindow) ? (int)$bookingWindow : 12;  // default 12 month window
        // $bookingWindowMonths = $bookingWindow;

        // maximum allowable appointment date
        $currentDate = new \DateTime();

        $maxBookingDate = clone $currentDate;

        $maxBookingDate->modify("+$bookingWindowMonths months");

        $appointmentDate = new \DateTime($appointmentDate);

        //check if the appointment is within booking window
        if ($appointmentDate <= $maxBookingDate) {
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



    // public static function checkExpireTime($appointment_date, $slot_start_time, $buffer_time)
    // {
    //     // Get current date and time
    //     $currentDate = date('Y-m-d');
    //     $currentTime = date('H:i:s');

    //     // Check if the appointment date is today
    //     if ($appointment_date == $currentDate) {
    //         // If the slot's start time is earlier than the current time, it's expired
    //         return $slot_start_time < $currentTime;
    //     }

    //     // For future dates, the slot is not expired
    //     return false;
    // }

    public static function checkExpireTime($appointment_date, $slot_start_time, int $buffer_time = 0)
    {
        // Get current date and time
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');

        // Check if the appointment date is today
        if ($appointment_date == $currentDate) {
            // Add buffer time to the current time
            $bufferedTime = date('H:i:s', strtotime("+$buffer_time minutes", strtotime($currentTime)));

            // If the slot's start time is earlier than the buffered time, it's expired
            return $slot_start_time < $bufferedTime;
        }

        // For future dates, the slot is not expired
        return false;
    }


    public static function checkOveride($user_id, $date, $slot, $priority = 3, $appointment = null)
    {
        // If there's an overlapping appointment, check if it can be overridden
        if ($appointment) {
            return Appointments::canOverride($appointment, $priority);
        }

        // If no appointment overlaps, no override is necessary
        return false;
    }
}
