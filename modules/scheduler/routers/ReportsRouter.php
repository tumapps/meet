<?php

return [
    /**
     * @OA\Get(path="/scheduler/reports",
     *   summary="Returns all statistical results for meetings",
     *   tags={"Report and Analytics"},
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all scheduler/appointments",
     *     @OA\JsonContent(
     *         @OA\Property(property="dataPayload", type="object",
     *             ),
     *         )
     *     )
     *   ),
     * )
     */
    'GET reports'     => 'reports/statistics',

     /**
     * @OA\Get(path="/scheduler/system-metrics",
     *   summary="lists system-metrics",
     *   tags={"Report and Analytics"},
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all scheduler/appointments",
     *     @OA\JsonContent(
     *         @OA\Property(property="dataPayload", type="object",
     *             ),
     *         )
     *     )
     *   ),
     * )
     */
    'GET system-metrics'     => 'reports/system-metrics',

    
     /**
     * @OA\Get(path="/scheduler/yearly-meeting-summary",
     *   summary="lists system-metrics",
     *   tags={"Report and Analytics"},
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all scheduler/appointments",
     *     @OA\JsonContent(
     *         @OA\Property(property="dataPayload", type="object",
     *             ),
     *         )
     *     )
     *   ),
     * )
     */
    'GET yearly-meeting-summary'     => 'reports/yearly-meeting-summary',

      /**
     * @OA\Get(path="/scheduler/weekly-meeting-summary",
     *   summary="lists system-metrics",
     *   tags={"Report and Analytics"},
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all scheduler/appointments",
     *     @OA\JsonContent(
     *         @OA\Property(property="dataPayload", type="object",
     *             ),
     *         )
     *     )
     *   ),
     * )
     */
    'GET weekly-meeting-summary'     => 'reports/weekly-meeting-summary',

    /**
     * @OA\Get(path="/scheduler/space-utilization",
     *   summary="lists system-metrics",
     *   tags={"Report and Analytics"},
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for space utilization",
     *     @OA\JsonContent(
     *         @OA\Property(property="dataPayload", type="object",
     *             ),
     *         )
     *     )
     *   ),
     * )
     */
    // 'GET system-metrics'     => 'reports/meeting-duration',
    'GET space-utilization'     => 'reports/space-utilization',

    /**
     * @OA\Get(path="/scheduler/upcoming-events",
     *   summary="lists all upcoming events sort by start date in ascending order",
     *   tags={"Report and Analytics"},
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for reports/upcoming-events",
     *     @OA\JsonContent(
     *         @OA\Property(property="dataPayload", type="object",
     *             ),
     *         )
     *     )
     *   ),
     * )
     */
    'GET upcoming-events'     => 'reports/upcoming-events',
];
