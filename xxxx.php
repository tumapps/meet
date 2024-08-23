 <?php
  // return $this->errorResponse([
            //         'message' => [
            //              'The VC is unavailable for the requested time slot',
            //         ],
            // ]);
            // 
            // 
            


            
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
 

  public static function getUnavailableSlots2($vc_id, $appointment_date, $start_time=null, $end_time=null)
    {
       $query = self::find()
        ->where(['user_id' => $vc_id])
        ->andWhere([
            'OR',
            // Check if the entire day is unavailable
            ['is_full_day' => true, 'start_date' => $appointment_date],
            // Check for time-slot-based unavailability within a date range
            ['AND',
                ['<=', 'start_date', $appointment_date],
                ['>=', 'end_date', $appointment_date],
            ]
        ]);

        // Add time-based conditions if start and end times are provided
        if ($start_time !== null && $end_time !== null) {
            $query->andWhere([
                'OR',
                ['AND', ['<=', 'start_time', $start_time], ['>', 'end_time', $start_time]],
                ['AND', ['<', 'start_time', $end_time], ['>=', 'end_time', $end_time]],
                ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
                ['AND', ['>=', 'start_time', $start_time], ['<=', 'end_time', $end_time]],
            ]);
        }

        return $query->all();
    }




    public static function getUnavailableSlots3($vc_id, $appointment_date, $start_time, $end_time)
    {
    // Step 1: Check if the entire day is unavailable
        $isFullDayUnavailable = self::find()
            ->where(['user_id' => $vc_id, 'is_full_day' => true, 'start_date' => $appointment_date])
            ->exists();

        if ($isFullDayUnavailable) {
            // If the entire day is unavailable, return all slots for that day
            return self::find()
                ->where(['user_id' => $vc_id, 'start_date' => $appointment_date])
                ->all();
        }

        // Step 2: Check for time-slot-based unavailability within a date range
        return self::find()
            ->where(['user_id' => $vc_id])
            ->andWhere([
                'AND',
                ['<=', 'start_date', $appointment_date],
                ['>=', 'end_date', $appointment_date],
            ])
            ->andWhere([
                'OR',
                ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $start_time]],
                ['AND', ['<=', 'start_time', $end_time], ['>=', 'end_time', $end_time]],
                ['AND', ['<=', 'start_time', $start_time], ['>=', 'end_time', $end_time]],
                ['AND', ['>=', 'start_time', $start_time], ['<=', 'end_time', $end_time]],
            ])
            ->all();
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