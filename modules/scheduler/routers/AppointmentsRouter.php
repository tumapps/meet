<?php
return [
    //security={{}} #disable authorization on an endpoint
    /**
     * @OA\Get(path="/scheduler/appointments",
     *   summary="Lists all Appointments models ",
     *   tags={"Appointments"},
     *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="User Id",in="query",name="_user_id", @OA\Schema(type="bigint")),
     *   @OA\Parameter(description="Date",in="query",name="_date", @OA\Schema(type="date")),
     *   @OA\Parameter(description="Start Time",in="query",name="_start_time", @OA\Schema(type="time")),
     *   @OA\Parameter(description="End Time",in="query",name="_end_time", @OA\Schema(type="time")),
     *   @OA\Parameter(description="Contact Name",in="query",name="_contact_name", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Email Address",in="query",name="_email_address", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Mobile Number",in="query",name="_mobile_number", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Subject",in="query",name="_subject", @OA\Schema(type="text")),
     *   @OA\Parameter(description="Appointment Type",in="query",name="_appointment_type", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Status",in="query",name="_status", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="timestamp")),
     *   @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="timestamp")),
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all scheduler/appointments",
     *     @OA\JsonContent(
     *         @OA\Property(property="dataPayload", type="object",
     *             @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/Appointments")),
     *             @OA\Property(property="countOnPage", type="integer", example="25"),
     *             @OA\Property(property="totalCount", type="integer",example="50"),
     *             @OA\Property(property="perPage", type="integer",example="25"),
     *             @OA\Property(property="totalPages", type="integer",example="2"),
     *             @OA\Property(property="currentPage", type="integer",example="1"),
     *             @OA\Property(property="paginationLinks", type="object",
     *                 @OA\Property(property="first", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *                 @OA\Property(property="previous", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *                 @OA\Property(property="self", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *                 @OA\Property(property="next", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *                 @OA\Property(property="last", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *             ),
     *         )
     *     )
     *   ),
     * )
     */
    'GET appointments'     => 'appointments/index',

    /**
     * @OA\Get(path="/scheduler/pending-appointments",
     *   summary="Lists all Pending Appointments models ",
     *   tags={"Appointments"},
     *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="User Id",in="query",name="_user_id", @OA\Schema(type="bigint")),
     *   @OA\Parameter(description="Date",in="query",name="_date", @OA\Schema(type="date")),
     *   @OA\Parameter(description="Start Time",in="query",name="_start_time", @OA\Schema(type="time")),
     *   @OA\Parameter(description="End Time",in="query",name="_end_time", @OA\Schema(type="time")),
     *   @OA\Parameter(description="Contact Name",in="query",name="_contact_name", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Email Address",in="query",name="_email_address", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Mobile Number",in="query",name="_mobile_number", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Subject",in="query",name="_subject", @OA\Schema(type="text")),
     *   @OA\Parameter(description="Appointment Type",in="query",name="_appointment_type", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Status",in="query",name="_status", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="timestamp")),
     *   @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="timestamp")),
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all scheduler/appointments",
     *     @OA\JsonContent(
     *         @OA\Property(property="dataPayload", type="object",
     *             @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/Appointments")),
     *             @OA\Property(property="countOnPage", type="integer", example="25"),
     *             @OA\Property(property="totalCount", type="integer",example="50"),
     *             @OA\Property(property="perPage", type="integer",example="25"),
     *             @OA\Property(property="totalPages", type="integer",example="2"),
     *             @OA\Property(property="currentPage", type="integer",example="1"),
     *             @OA\Property(property="paginationLinks", type="object",
     *                 @OA\Property(property="first", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *                 @OA\Property(property="previous", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *                 @OA\Property(property="self", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *                 @OA\Property(property="next", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *                 @OA\Property(property="last", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *             ),
     *         )
     *     )
     *   ),
     * )
     */
    'GET pending-appointments' => 'appointments/pending-appointments',

    /**
     * @OA\Post(
     *     path="/scheduler/get-slots",
     *     summary="Get all available slots ",
     *     tags={"Appointments"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Fill in appointments data",
     *         @OA\JsonContent(
     *             required={"user_id","date"},
     *             ref="#/components/schemas/Appointments",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Data payload",
     *         @OA\JsonContent(
     *             @OA\Property(property="dataPayload", type="object",
     *                 @OA\Property(property="data", type="object",ref="#/components/schemas/Appointments"),
     *                 @OA\Property(property="toastMessage", type="string", example="appointments created successfully"),
     *                 @OA\Property(property="toastTheme", type="string",example="success"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Data Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errorPayload", type="object",
     *                 @OA\Property(property="errors", type="object", ref="#/components/schemas/Appointments"),
     *                 @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *                 @OA\Property(property="toastTheme", type="string",example="danger"),
     *             )
     *         )
     *     )
     * )
     */
    'POST get-slots'       => 'appointments/get-slots',

    /**
     * @OA\Post(
     *     path="/scheduler/sms",
     *     summary="Send SMS for an appointment",
     *     tags={"Appointments"},
     *     @OA\Response(
     *         response=200,
     *         description="SMS sent successfully",
     *     )
     * )
     */
    'POST sms' => 'appointments/send-sms',

    /**
     * @OA\Get(path="/scheduler/slot-suggestion/{id}",
     *   summary="Display the next available slots for a particular affected appointment",
     *   tags={"Appointments"},
     *   @OA\Parameter(description="Appointments unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *   @OA\Response(
     *     response=200,
     *     description="Displays a single Appointments model.",
     *     @OA\JsonContent(
     *         @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/Appointments")
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Resource not found",
     *     @OA\JsonContent(
     *         @OA\Property(property="errorPayload", type="object",
     *             @OA\Property(property="statusCode", type="integer", example=404),
     *             @OA\Property(property="errorMessage", type="string", example="Not found")
     *         )
     *     )
     *   ),
     * )
     */
    'GET slot-suggestion/{id}'       => 'appointments/suggest-available-slots',

    /**
     * @OA\Post(
     *     path="/scheduler/self-booking",
     *     summary="Self-booking for an appointment",
     *     tags={"Appointments"},
     *     @OA\Response(
     *         response=200,
     *         description="Self-booking successful",
     *     )
     * )
     */
    'POST self-booking'        => 'appointments/self-book',

    /**
     * @OA\Get(
     *     path="/scheduler/types",
     *     summary="Get meeting types",
     *     tags={"Appointments"},
     *     @OA\Response(
     *         response=200,
     *         description="Returns a list of meeting types",
     *     )
     * )
     */
    'GET types'  => 'appointments/meeting-types',

    /**
     * @OA\Get(path="/scheduler/priorities",
     *   summary="Get appointment priority labels and codes",
     *   tags={"Appointment Priorities"},
     *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all scheduler/priorities",
     *     @OA\JsonContent(
     *         @OA\Property(property="dataPayload", type="object",
     *             @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/Appointments")),
     *             @OA\Property(property="countOnPage", type="integer", example="25"),
     *             @OA\Property(property="totalCount", type="integer",example="50"),
     *             @OA\Property(property="perPage", type="integer",example="25"),
     *             @OA\Property(property="totalPages", type="integer",example="2"),
     *             @OA\Property(property="currentPage", type="integer",example="1"),
     *             @OA\Property(property="paginationLinks", type="object",
     *                 @OA\Property(property="first", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *                 @OA\Property(property="previous", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *                 @OA\Property(property="self", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *                 @OA\Property(property="next", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *                 @OA\Property(property="last", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *             ),
     *         )
     *     )
     *   ),
     * )
     */
    'GET priorities' => 'appointments/get-priorities',

    /**
     * @OA\Post(
     *     path="/scheduler/appointments",
     *     summary="Creates a new Appointments model ",
     *     tags={"Appointments"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Fill in appointments data",
     *         @OA\JsonContent(
     *             required={"id","date","email_address"},
     *             ref="#/components/schemas/Appointments",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Data payload",
     *         @OA\JsonContent(
     *             @OA\Property(property="dataPayload", type="object",
     *                 @OA\Property(property="data", type="object",ref="#/components/schemas/Appointments"),
     *                 @OA\Property(property="toastMessage", type="string", example="appointments created successfully"),
     *                 @OA\Property(property="toastTheme", type="string",example="success"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Data Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errorPayload", type="object",
     *                 @OA\Property(property="errors", type="object", ref="#/components/schemas/Appointments"),
     *                 @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *                 @OA\Property(property="toastTheme", type="string",example="danger"),
     *             )
     *         )
     *     )
     * )
     */
    'POST appointments'         => 'appointments/create',

    /**
     * @OA\Post(
     *     path="/scheduler/attendance-confirmation/{appointment_id}/{attendee_id}",
     *     summary="Confirm attendance for an appointment",
     *     tags={"Appointments"},
     *     @OA\Response(
     *         response=200,
     *         description="Attendance confirmed successfully",
     *     )
     * )
     */
    'POST attendance-confirmation/{appointment_id}/{attendee_id}' => 'appointments/confirm-attendance',

    /**
     * @OA\Put(path="/scheduler/checkin/{id}",
     *   summary="Mark an appointment as Attended",
     *   tags={"Appointments"},
     *   @OA\Parameter(description="Appointments unique ID to find the appointment",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *   @OA\Response(
     *     response=200,
     *     description="Finds a single Appointments model.",
     *     @OA\JsonContent(
     *         @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/Appointments")
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Resource not found",
     *     @OA\JsonContent(
     *         @OA\Property(property="errorPayload", type="object",
     *             @OA\Property(property="statusCode", type="integer", example=404),
     *             @OA\Property(property="errorMessage", type="string", example="Not found")
     *         )
     *     )
     *   ),
     * )
     */
    'PUT checkin/{id}' => 'appointments/checkin',

    /**
     * @OA\Get(path="/scheduler/appointments/{id}",
     *   summary="Displays a single Appointments model",
     *   tags={"Appointments"},
     *   @OA\Parameter(description="Appointments unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *   @OA\Response(
     *     response=200,
     *     description="Displays a single Appointments model.",
     *     @OA\JsonContent(
     *         @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/Appointments")
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Resource not found",
     *     @OA\JsonContent(
     *         @OA\Property(property="errorPayload", type="object",
     *             @OA\Property(property="statusCode", type="integer", example=404),
     *             @OA\Property(property="errorMessage", type="string", example="Not found")
     *         )
     *     )
     *   ),
     * )
     */
    'GET appointments/{id}'     => 'appointments/view',

    /**
     * @OA\Get(
     *     path="/scheduler/space-details",
     *     summary="Get space details",
     *     tags={"Appointments"},
     *     @OA\Response(
     *         response=200,
     *         description="Returns space details",
     *     )
     * )
     */
    'GET space-details' => 'appointments/space-details',

    /**
     * @OA\Put(
     *     path="/scheduler/appointments/{id}",
     *     tags={"Appointments"},
     *     summary="Updates an existing Appointments model",
     *     @OA\Parameter(description="Appointments unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Finds the Appointments model to be updated based on its primary key value",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Appointments",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Data payload",
     *         @OA\JsonContent(
     *             @OA\Property(property="dataPayload", type="object",
     *                 @OA\Property(property="data", type="object",ref="#/components/schemas/Appointments"),
     *                 @OA\Property(property="toastMessage", type="string", example="appointments updated successfully"),
     *                 @OA\Property(property="toastTheme", type="string",example="success"),
     *             )
     *         )
     *     ),
     * )
     */
    'PUT appointments/{id}'     => 'appointments/update',

    /**
     * @OA\Put(
     *     path="/scheduler/approve/{id}",
     *     tags={"Appointments"},
     *     summary="Approve an existing Appointments model with a status pending",
     *     @OA\Parameter(description="Appointments unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Finds the Appointments model to be approved based on its primary key value",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Appointments",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Data payload",
     *         @OA\JsonContent(
     *             @OA\Property(property="dataPayload", type="object",
     *                 @OA\Property(property="data", type="object",ref="#/components/schemas/Appointments"),
     *                 @OA\Property(property="toastMessage", type="string", example="appointments approved successfully"),
     *                 @OA\Property(property="toastTheme", type="string",example="success"),
     *             )
     *         )
     *     ),
     * )
     */
    'PUT approve/{id}' => 'appointments/approve',

    /**
     * @OA\Put(
     *     path="/scheduler/reject/{id}",
     *     tags={"Appointments"},
     *     summary="Reject an existing Appointments model with a status pending",
     *     @OA\Parameter(description="Appointments unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Finds the Appointments model to be rejected based on its primary key value",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Appointments",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Data payload",
     *         @OA\JsonContent(
     *             @OA\Property(property="dataPayload", type="object",
     *                 @OA\Property(property="data", type="object",ref="#/components/schemas/Appointments"),
     *                 @OA\Property(property="toastMessage", type="string", example="appointments rejected successfully"),
     *                 @OA\Property(property="toastTheme", type="string",example="success"),
     *             )
     *         )
     *     ),
     * )
     */
    'PUT reject/{id}' => 'appointments/reject',

    /**
     * @OA\Put(
     *     path="/scheduler/cancel/{id}",
     *     tags={"Appointments"},
     *     summary="Cancel an existing Appointments model",
     *     @OA\Parameter(description="Appointments unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Finds the Appointments model to be canceled based on its primary key value",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Appointments",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Data payload",
     *         @OA\JsonContent(
     *             @OA\Property(property="dataPayload", type="object",
     *                 @OA\Property(property="data", type="object",ref="#/components/schemas/Appointments"),
     *                 @OA\Property(property="toastMessage", type="string", example="appointments canceled successfully"),
     *                 @OA\Property(property="toastTheme", type="string",example="success"),
     *             )
     *         )
     *     ),
     * )
     */
    'PUT cancel/{id}'     => 'appointments/cancel',

    /**
     * @OA\Put(
     *     path="/scheduler/remove-attendee/{id}",
     *     tags={"Appointments"},
     *     summary="Deletes an existing Appointments Attendee model.",
     *     @OA\Parameter(description="Appointments unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *     @OA\Response(
     *         response=202,
     *         description="Deletion successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="dataPayload", type="object")
     *         )
     *     ),
     * )
     */
    'PUT remove-attendee/{id}' => 'appointments/remove-attendee',

    /**
     * @OA\Delete(path="/scheduler/appointments/{id}",
     *    tags={"Appointments"},
     *    summary="Deletes an existing Appointments model.",
     *     @OA\Parameter(description="Appointments unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *     @OA\Response(
     *         response=202,
     *         description="Deletion successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="dataPayload", type="object")
     *         )
     *     ),
     * )
     */
    'DELETE appointments/{id}'  => 'appointments/delete',

    /**
     * @OA\Patch(path="/scheduler/appointments/{id}",
     *    tags={"Appointments"},
     *    summary="Restores a deleted Appointments model.",
     *     @OA\Parameter(description="Appointments unique ID to restore",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *     @OA\Response(
     *         response=202,
     *         description="Restoration successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="dataPayload", type="object")
     *         )
     *     ),
     * )
     */
    'PATCH appointments/{id}'  => 'appointments/delete',
];
