 <?php

 public static function getAvailableSlots($user_id, $date)
    {
        $allSlots = self::generateTimeSlots($user_id);
         $slotsWithAvailability = [];
        // $availableSlots = [];

        foreach ($allSlots as $slot) {
            // if (self::isSlotAvailable($user_id, $date, $slot)) {
                // $availableSlots[] = $slot;
                $isAvailable = self::isSlotAvailable($user_id, $date, $slot);
                $slotsWithAvailability[] = [
                    'slot' => $slot,
                    'isAvailable' => $isAvailable
                ];
            // }
        }

        // return $availableSlots;
        return $slotsWithAvailability;
    }
 /check if the appointment is within booking window
        if(/*$appointmentDate >= $currentDate && */$appointmentDate <= $maxBookingDate){
            return true; // valid appointment date
        }
        return false; //invalid appointment, outside the boking window
            
if(empty($bookingWindow)){
            return 'Booking window is not set';
        }

            
 // $appointmentStart = new \DateTime("$appointment_date $start_time");
        // $appointmentEnd = new \DateTime("$appointment_date $end_time");

        // foreach ($bookedSlots as $slot) {
        //     $slotStart = new \DateTime("$appointment_date {$slot->start_time}");
        //     $slotEnd = new \DateTime("$appointment_date {$slot->end_time}");

        //     if ($appointmentStart < $slotEnd && $appointmentEnd > $slotStart) {
        //         return false;
        //     }
        // }
        // 
        // 
        // 
 

   



  public static function findNextAvailableSlot($user_id, $appointment_date, $startTime, $endTime)
    {
        // Get slot duration for a given time range
        $slotDuration = TimeHelper::calculateDuration(new \DateTime($startTime), new \DateTime($endTime));
        $date = $appointment_date;

        // while (true) {
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
            $nextDate = date('Y-m-d', strtotime($date . ' +1 day'));
            // return $date;
            return self::findNextAvailableSlot($user_id, $nextDate, $startTime, $endTime);
        // }
    }

   // retuning all booked time slots logic

// return self::find()
//         ->where(['user_id' => $vc_id])
//         ->andWhere([
//             'OR',
//             // Check if the entire day is unavailable
//             ['AND',['is_full_day' => true, 'start_date' => $appointment_date]],
//             [
//                 'AND',
//                 ['<=', 'start_date', $appointment_date],
//                 ['>=', 'end_date', $appointment_date],
//                 [
//                     'OR',
//                     ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $start_time]],
//                     ['AND', ['<=', 'start_time', $end_time], ['>=', 'end_time', $end_time]],
//                     ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
//                     ['AND', ['>=', 'start_time', $start_time], ['<=', 'end_time', $end_time]],
//                 ]
//             ]
//         ])
//         ->all();
//         
//         
//         
//         
//         
//         Purpose of Calculating Available Slots:

The reason you're calculating available slots is to find gaps in the VC's schedule where appointments can be booked. If there are no appointments or blocked slots, the entire working day is available for booking. By identifying these gaps, you can find the best time to reschedule an appointment.



Why Calculate Available Slots:

    Dynamic Availability: Available slots are dynamic because they depend on both the VC's working hours and the times that are already booked or blocked. By calculating the available slots based on these factors, you ensure that only the truly available times are offered for rescheduling.
    Flexibility: This approach allows the VC to adjust their working hours while still respecting existing bookings and blocked times.


    public static function getUnavailableSlots($vc_id, $appointment_date, $start_time, $end_time)
    {
        // Check if the entire day is unavailable
        $isFullDayUnavailable = self::find()
            ->where(['user_id' => $vc_id, 'is_full_day' => true, 'start_date' => $appointment_date])
            ->exists();

        if ($isFullDayUnavailable) {
            // If the entire day is unavailable, return true ie indicating unavailability
            return true;
        }

        // Check for time-slot-based unavailability within a date range
        $hasOverlappingSlots = self::find()
            ->where(['user_id' => $vc_id])
            ->andWhere([
                'AND',
                ['<=', 'start_date', $appointment_date],
                ['>=', 'end_date', $appointment_date],
            ])
            ->andWhere([
                'OR',
                ['AND', ['<=', 'start_time', $start_time], ['>', 'end_time', $start_time]],
                ['AND', ['<', 'start_time', $end_time], ['>=', 'end_time', $end_time]],
                ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
                ['AND', ['>=', 'start_time', $start_time], ['<=', 'end_time', $end_time]],
            ])
            ->exists();

        // Return true if there are overlapping slots, otherwise false
        return $hasOverlappingSlots;
    }




    return self::find()
        ->where(['user_id' => $user_id])
        ->andWhere([
            'OR',
            // Appointments within the unavailable date range
            ['AND',
                ['<=', 'appointment_date', $end_date],
                ['>=', 'appointment_date', $start_date],
            ],
            // Appointments that overlap with unavailable time on the same day
            [
                'AND',
                ['between', 'appointment_date', $start_date, $end_date],
                [
                    'OR',
                    ['AND', ['<=', 'start_time', $end_time], ['>=', 'start_time', $start_time]],
                    ['AND', ['<=', 'end_time', $end_time], ['>=', 'end_time', $start_time]],
                    ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
                ]
            ]
        ])
        ->orderBy(['


overlapping
        return self::find()
        ->where(['user_id' => $user_id])
        ->andWhere([
            'AND',
            // Appointments within the unavailable date range
            ['>=', 'appointment_date', $start_date],
            ['<=', 'appointment_date', $end_date],
        ])
        ->andWhere([
            'OR',
            // Case 1: Appointment starts within the unavailable time range
            ['AND', ['>=', 'start_time', $start_time], ['<', 'start_time', $end_time]],
            // Case 2: Appointment ends within the unavailable time range
            ['AND', ['>', 'end_time', $start_time], ['<=', 'end_time', $end_time]],
            // Case 3: Appointment completely overlaps with unavailable time range
            ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
            // Case 4: Appointment starts before unavailability ends but ends after
            ['AND', ['<', 'start_time', $end_time], ['>', 'end_time', $end_time]],
        ])
        ->orderBy(['created_at' => SORT_ASC])
        ->all();




        $isAvailable = $this->checkAvailability(
            $dataRequest['Appointments']['user_id'], 
            $dataRequest['Appointments']['appointment_date'], 
            $dataRequest['Appointments']['start_time'],
            $dataRequest['Appointments']['end_time']
        );

        if(!$isAvailable){
            return $this->payloadResponse(['message' => 'The VC is unavailable for the requested time slot',]);
        }





// protected static function calculateAvailableSlots($user_id, $bookedSlots, $unavailableSlots, $date, $startTime, $endTime, $slotDuration)
    // {

    //     // Convert slot duration from minutes to seconds
    //     $slotDurationInSeconds = $slotDuration * 60;

    //     // Get working hours for the user
    //     $workingHours = Settings::getWorkingHours($user_id);
    //     $dayStart = strtotime($date . ' ' . $workingHours['start_time']);
    //     $dayEnd = strtotime($date . ' ' . $workingHours['end_time']);


    //     $availableSlots = [];
    //     $previousEnd = $dayStart;

    //     // return [
    //     //   'unavailable' => $unavailableSlots,
    //     //   'booked' => $bookedSlots
    //     // ];
    //     // Merge booked and unavailable slots and sort by start time
    //     $allSlots = array_merge($unavailableSlots, $bookedSlots);
    //     // return ['all' =>  $allSlots];

    //     usort($allSlots, function($a, $b) {
    //         return strtotime($a['start_time']) - strtotime($b['start_time']);
    //     });

    //     // return ['all sorted' =>  $allSlots];


    //     // Iterate over the slots to find gaps
    //     foreach ($allSlots as $slot) {
    //      // return [
    //      //  'slotsst' => $slot['start_time'],
    //      //  'sloteet' => $slot['end_time'],

    //      // ];
    //         $slotStart = strtotime($slot['start_time']);
    //         $slotEnd = strtotime($slot['end_time']);

    //         // Check if there's a gap between the previous end time and the start of the next booked unavailable slot
    //         return $previousEnd + $slotDurationInSeconds;
    //         while ($previousEnd + $slotDurationInSeconds <= $slotStart) {
    //             $availableSlots[] = [
    //                 'date' => $date,
    //                 'start_time' => date('H:i:s', $previousEnd),
    //                 'end_time' => date('H:i:s', $previousEnd + $slotDurationInSeconds)
    //             ];
    //             $previousEnd += $slotDurationInSeconds;
    //         }

    //         // Update the previous end time to the current slot's end time
    //         $previousEnd = max($previousEnd, $slotEnd);
            
    //     }

    //     // Finally, check the gap between the last slot and the end of the working day
    //     while ($previousEnd + $slotDurationInSeconds <= $dayEnd) {

    //         $availableSlots[] = [
    //             'date' => $date,
    //             'start_time' => date('H:i:s', $previousEnd),
    //             'end_time' => date('H:i:s', $previousEnd + $slotDurationInSeconds)
    //         ];
    //         $previousEnd += $slotDurationInSeconds;
    //     }

    //     return $availableSlots;
    // }
    // 




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







    public function actionSelfBook()
    {
        // Ensure the user is logged in (i.e., is the VC)
        // if (Yii::$app->user->isGuest) {
        //     return $this->errorResponse(['message' => 'You must be logged in to book an appointment.']);
        // }
        
        $model = new Appointments();
        $model->loadDefaultValues();
        $dataRequest['Appointments'] = Yii::$app->request->getBodyParams();

        // $userId = Yii::$app->user->identity->getId();

        //$dataRequest['Appointments']['user_id'] = $userId
        $user_id = $dataRequest['Appointments']['user_id'];
        $start_date = $dataRequest['Appointments']['appointment_date'];
        $end_date = $dataRequest['Appointments']['appointment_date'];
        $start_time = $dataRequest['Appointments']['start_time'];
        $end_time = $dataRequest['Appointments']['end_time'];

        $dataRequest['Appointments']['email_address'] = 'adminvc@gmail.com';


        // trigger rescheduling logic 
        $appointments = Ar::rescheduleAffectedAppointments(
                $user_id, 
                $start_date, 
                $end_date, 
                $start_time, 
                $end_time
        );

        // return $appointments;

        $model->status = 'self';

        if($model->load($dataRequest) && $model->save()) {
                $data = [
                    $model,
                    'affectedAppointments' => $appointments
                ];
                return $this->payloadResponse(
                    $data, ['statusCode' => 201, 'message' => 'self-booking completed successfully']
               );
        }
        return $this->errorResponse($model->getErrors());
         
    }




   protected static function calculateAvailableSlots($user_id, $bookedSlots, $unavailableSlots, $date, $startTime, $endTime, $slotDuration)
    {
        // Convert slot duration from minutes to seconds
        $slotDurationInSeconds = $slotDuration * 60;

        // Get working hours for the user
        $workingHours = Settings::getWorkingHours($user_id);
        $dayStart = strtotime($date . ' ' . $workingHours['start_time']);
        $dayEnd = strtotime($date . ' ' . $workingHours['end_time']);

        // Initialize array to store all possible time slots for the day
        $suggestedSlots = [];
        // for ($time = $dayStart; $time + $slotDurationInSeconds <= $dayEnd; $time += $slotDurationInSeconds) {
        //     $suggestedSlots[] = [
        //         'start_time' => $time,
        //         'end_time' => $time + $slotDurationInSeconds
        //     ];
        // }
        $slots = TimeHelper::generateTimeSlots($user_id);


        // Helper function to filter out slots
        $filterSlots = function ($slots, &$suggestedSlots) {
            foreach ($slots as $slot) {
                $slotStart = strtotime($slot['start_time']);
                $slotEnd = strtotime($slot['end_time']);

                // Remove overlapping slots from suggestedSlots
                $suggestedSlots = array_filter($suggestedSlots, function ($suggestedSlot) use ($slotStart, $slotEnd) {
                    return !($suggestedSlot['start_time'] < $slotEnd && $suggestedSlot['end_time'] > $slotStart);
                });
            }
        };

        // Filter out unavailable time ranges
        $filterSlots($unavailableSlots, $suggestedSlots);

        // Filter out booked time slots
        $filterSlots($bookedSlots, $suggestedSlots);

        // Format available slots as needed
        $availableSlots = array_map(function ($slot) use ($date) {
            return [
                'date' => $date,
                'start_time' => date('H:i:s', $slot['start_time']),
                'end_time' => date('H:i:s', $slot['end_time']),
            ];
        }, $suggestedSlots);

        return $availableSlots;
    }