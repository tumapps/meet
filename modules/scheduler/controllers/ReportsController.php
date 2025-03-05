<?php

namespace scheduler\controllers;

use scheduler\models\Appointments;
use scheduler\models\Space;
use scheduler\models\SpaceAvailability;



class ReportsController extends \helpers\ApiController
{

    public function actionStatistics()
    {
        $totalAppointments = Appointments::find()->count();
        $scheduled = Appointments::find()->where(['status' => Appointments::STATUS_RESCHEDULED])->count();
        $canceled = Appointments::find()->where(['status' => Appointments::STATUS_CANCELLED])->count();
        $rejected = Appointments::find()->where(['status' => Appointments::STATUS_REJECTED])->count();
        $completed = Appointments::find()->where(['status' => Appointments::STATUS_ATTENDED])->count();
        $upcoming  = Appointments::upComingAppointments();
        $missed  = Appointments::find()->where(['status' => Appointments::STATUS_MISSED])->count();
        $onHold = Appointments::find()->where(['status' => Appointments::STATUS_RESCHEDULE])->count();
        $active = Appointments::find()->where(['status' => Appointments::STATUS_ACTIVE])->count();
        $pending = Appointments::find()->where(['status' => Appointments::STATUS_PENDING])->count();

        return $this->payloadResponse([
            'total_appointments' => $totalAppointments,
            'active' => $active,
            'scheduled' => $scheduled,
            'canceled' => $canceled,
            'rejected' => $rejected,
            'attended' => $completed,
            'pending' => $pending,
            'missed' => $missed,
            'on_hold' => $onHold,
            'upcoming' => $upcoming,
        ]);
    }
    public function actionSystemMetrics()
    {
        $avgDuration = Appointments::find()
            ->select(['AVG(EXTRACT(EPOCH FROM (end_time - start_time)) / 60) AS avg_duration'])
            ->scalar();

        $totalMeetingMinutes = Appointments::find()
            ->select(['CAST(SUM(EXTRACT(EPOCH FROM (end_time - start_time)) / 60) AS INTEGER) AS total_minutes'])
            ->scalar();


        // Peak meeting hours
        $peakHours = Appointments::find()
            ->select(['EXTRACT(HOUR FROM start_time) AS hour, COUNT(*) AS count'])
            ->groupBy(['hour'])
            ->orderBy(['count' => SORT_DESC])
            ->limit(3)
            ->asArray()
            ->all();

        return $this->payloadResponse([
            'global_trends' => [
                'total_minutes_for_all_minutes' => $totalMeetingMinutes,
                'average_duration' => round((float) $avgDuration, 2), // Round to 2 decimal places
                'peak_meeting_hours' => $peakHours,
            ]
        ]);
    }


    public function actionUserReports($user_id, $period = 'month')
    {
        $allowedPeriods = ['day', 'week', 'month'];

        // Validate the period input
        if (!in_array($period, $allowedPeriods, true)) {
            return $this->errorResponse(['error' => ['Invalid period. Allowed values: day, week, month']]);
        }

        // Define the date range
        $startDate = new \DateTime();
        $intervalMapping = [
            'day' => 'P1D',
            'week' => 'P7D',
            'month' => 'P1M'
        ];
        $startDate->sub(new \DateInterval($intervalMapping[$period]));

        // Meeting Durations
        $meetingDurations = Appointments::find()
            ->select([
                "COUNT(id) AS total_meetings",
                "SUM(EXTRACT(EPOCH FROM (end_time - start_time)) / 3600) AS total_hours",
                "ROUND(AVG(EXTRACT(EPOCH FROM (end_time - start_time)) / 60), 2) AS avg_minutes"
            ])
            ->where(['user_id' => $user_id])
            ->andWhere(['>=', 'start_time', $startDate->format('Y-m-d')])
            ->asArray()
            ->one();

        // Attendance Insights
        $attendance = Appointments::find()
            ->select([
                "COUNT(id) AS total_scheduled",
                "SUM(CASE WHEN status = 6 THEN 1 ELSE 0 END) AS attended",
                "SUM(CASE WHEN status = 9 THEN 1 ELSE 0 END) AS missed"
            ])
            ->where(['user_id' => $user_id])
            ->andWhere(['>=', 'start_time', $startDate->format('Y-m-d')])
            ->asArray()
            ->one();

        $attendance['attendance_percentage'] = ($attendance['total_scheduled'] > 0)
            ? round(($attendance['attended'] / $attendance['total_scheduled']) * 100, 2)
            : 0;

        // Recurring Meeting Frequency
        // $recurringMeetings = Appointments::find()
        //     ->select([
        //         "COUNT(DISTINCT recurring_id) AS recurring_meetings"
        //     ])
        //     ->where(['user_id' => $user_id])
        //     ->andWhere(['is not', 'recurring_id', null])
        //     ->andWhere(['>=', 'start_time', $startDate->format('Y-m-d')])
        //     ->asArray()
        //     ->one();

        // Most Frequent Collaborators
        $frequentCollaborators = (new \yii\db\Query())
            ->select([
                "collaborator_id",
                "COUNT(*) AS meeting_count"
            ])
            ->from('appointment_attendees')
            ->where(['user_id' => $user_id])
            ->andWhere(['>=', 'start_time', $startDate->format('Y-m-d')])
            ->groupBy('collaborator_id')
            ->orderBy(['meeting_count' => SORT_DESC])
            ->limit(5)
            ->all();

        // Personal Productivity Metrics
        $productivity = Appointments::find()
            ->select([
                "COUNT(id) AS scheduled_meetings",
                "TO_CHAR(start_time, 'HH24') AS productive_hour",
                "SUM(EXTRACT(EPOCH FROM (end_time - start_time)) / 3600) AS time_in_meetings"
            ])
            ->where(['user_id' => $user_id])
            ->andWhere(['>=', 'start_time', $startDate->format('Y-m-d')])
            ->groupBy(['productive_hour'])
            ->orderBy(['time_in_meetings' => SORT_DESC])
            ->limit(1)
            ->asArray()
            ->one();

        // Collaboration Metrics
        $collaborationMetrics = (new \yii\db\Query())
            ->select([
                "COUNT(DISTINCT attendee_id) AS unique_attendees",
                "COUNT(DISTINCT department_id) AS cross_department_collaborations"
            ])
            ->from('appointment_attendees')
            ->where(['user_id' => $user_id])
            ->andWhere(['>=', 'start_time', $startDate->format('Y-m-d')])
            ->asArray()
            ->one();

        // Final Report Response
        return $this->payloadResponse([
            'meeting_durations' => $meetingDurations,
            'attendance_insights' => $attendance,
            // 'recurring_meetings' => $recurringMeetings,
            'frequent_collaborators' => $frequentCollaborators,
            'personal_productivity' => $productivity,
            'collaboration_metrics' => $collaborationMetrics
        ]);
    }


    public function actionWeeklyMeetingSummary()
    {
        $weekRange = $this->getCurrentWeekRange();
        $startDate = $weekRange['start_of_week'];
        $endDate = $weekRange['end_of_week'];

        $appointmentsQuery = Appointments::find()
            ->where(['between', 'appointment_date', $startDate, $endDate])
            ->asArray();
        // ->all();

        $appointments = $appointmentsQuery->all();
        $appointmentsCount = $appointmentsQuery->count();

        $result = [];

        // Create a DatePeriod to iterate through each date in the range
        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            (new \DateTime($endDate))->modify('+1 day')
        );

        // Initialize counts for each date
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $result[$formattedDate] = [
                'total_meetings' => 0,
                'attended_meetings' => 0,
                'canceled_meetings' => 0,
                'missed_meetings' => 0,
                'rejected_meetings' => 0,
                'rescheduled_meetings' => 0,
                'on_hold_meetings' => 0,
                'pending_meetings' => 0,
            ];
        }

        // Count appointments by date and status
        foreach ($appointments as $appointment) {
            $appointmentDate = $appointment['appointment_date'];
            $status = $appointment['status'];

            if (isset($result[$appointmentDate])) {
                $result[$appointmentDate]['total_meetings']++;

                switch ($status) {
                    case 6:
                        $result[$appointmentDate]['attended_meetings']++;
                        break;
                    case 4:
                        $result[$appointmentDate]['canceled_meetings']++;
                        break;
                    case 9:
                        $result[$appointmentDate]['missed_meetings']++;
                        break;
                    case 2:
                        $result[$appointmentDate]['rejected_meetings']++;
                        break;
                    case 5:
                        $result[$appointmentDate]['rescheduled_meetings']++;
                        break;
                    case 3:
                        $result[$appointmentDate]['on_hold_meetings']++;
                        break;
                    case 11:
                        $result[$appointmentDate]['pending_meetings']++;
                        break;
                }
            }
        }

        return $this->payloadResponse([
            'total_meetings' => $appointmentsCount,
            'weekly_meeting_summary' => $result
        ]);
    }


    private function getCurrentWeekRange()
    {
        $currentDate = date('Y-m-d');

        $daysFromMonday = date('N', strtotime($currentDate)) - 1;
        $startDate = date('Y-m-d', strtotime("-$daysFromMonday days", strtotime($currentDate)));

        $daysToSunday = 7 - date('N', strtotime($currentDate));
        $endDate = date('Y-m-d', strtotime("+$daysToSunday days", strtotime($currentDate)));

        return [
            'start_of_week' => $startDate,
            'end_of_week' => $endDate
        ];
    }

    public function actionYearlyMeetingSummary($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $meetingData = Appointments::find()
            ->select([
                "TO_CHAR(appointment_date, 'YYYY-MM') AS month",
                "COUNT(id) AS total_meetings"
            ])
            ->where(['between', 'appointment_date', "$year-01-01", "$year-12-31"])
            ->groupBy(["TO_CHAR(appointment_date, 'YYYY-MM')"])
            ->orderBy(["month" => SORT_ASC])
            ->asArray()
            ->all();

        $yearlyMeetings = [];
        $monthMap = []; // To map "YYYY-MM" to month names

        for ($i = 1; $i <= 12; $i++) {
            $month = sprintf('%04d-%02d', $year, $i); // Format: YYYY-MM
            $date = new \DateTime("$year-$i-01"); // Create date object for the first day of the month
            $monthName = $date->format('M'); // Get full month name (e.g., January)

            // Store the mapping for later use
            $monthMap[$month] = $monthName;

            // Initialize with 0 meetings
            $yearlyMeetings[$month] = [
                'total_meetings' => 0
            ];
        }

        // Populate actual data
        foreach ($meetingData as $data) {
            $yearlyMeetings[$data['month']] = [
                'total_meetings' => (int)$data['total_meetings']
            ];
        }

        // Transform keys from YYYY-MM to month names
        $formattedMeetings = [];
        foreach ($yearlyMeetings as $month => $data) {
            $formattedMeetings[$monthMap[$month]] = $data;
        }

        return $this->payloadResponse([
            'yearly_meeting_summary' => $formattedMeetings
        ]);
    }


    public function actionSummary($filter = 'monthly')
    {
        // Placeholder for summary report logic
    }

    public function actionMeetings($status = null)
    {
        $query = Appointments::find();

        if ($status) {
            $query->where(['status' => $status]);
        }

        return $query->asArray()->all();
    }

    /**
     * Get space utilization metrics
     * @return array
     */
    public function actionSpaceUtilization()
    {
        $spaces = Space::find()->all();
        $utilizationData = [];

        foreach ($spaces as $space) {
            // Total bookings for the space
            $totalBookings = SpaceAvailability::find()->where(['space_id' => $space->id])->count();

            // Get peak usage hour
            $peakUsage = SpaceAvailability::find()
                ->alias('sa')
                ->innerJoin('appointments a', 'a.id = sa.space_id') // Join to appointments
                ->select(['EXTRACT(HOUR FROM sa.start_time) as hour', 'COUNT(*) as count'])
                ->where(['sa.space_id' => $space->id])
                ->groupBy(['hour'])
                ->orderBy(['count' => SORT_DESC])
                ->limit(1)
                ->asArray()
                ->one();

            $utilizationData[] = [
                'space' => $space->name,
                'total_bookings' => $totalBookings,
                'peak_usage_hour' => $peakUsage['hour'] ?? null,
                'peak_usage_count' => $peakUsage['count'] ?? 0
            ];
        }

        return $this->payloadResponse([
            'space_utilization' => $utilizationData
        ]);
    }
}
