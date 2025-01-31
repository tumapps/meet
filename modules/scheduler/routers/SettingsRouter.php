<?php
return [
    //security={{}} #disable authorization on an endpoint

    /**
     * @OA\Get(path="/scheduler/settings",
     *   summary="Lists all Settings models ",
     *   tags={"Settings"},
     *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="User Id",in="query",name="_user_id", @OA\Schema(type="bigint")),
     *   @OA\Parameter(description="Start Time",in="query",name="_start_time", @OA\Schema(type="date")),
     *   @OA\Parameter(description="End Time",in="query",name="_end_time", @OA\Schema(type="date")),
     *   @OA\Parameter(description="Slot Duration",in="query",name="_slot_duration", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Booking Window",in="query",name="_booking_window", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all scheduler/settings",
     *     @OA\JsonContent(
     *         @OA\Property(property="dataPayload", type="object",
     *             @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/Settings")),
     *             @OA\Property(property="countOnPage", type="integer", example="25"),
     *             @OA\Property(property="totalCount", type="integer",example="50"),
     *             @OA\Property(property="perPage", type="integer",example="25"),
     *             @OA\Property(property="totalPages", type="integer",example="2"),
     *             @OA\Property(property="currentPage", type="integer",example="1"),
     *             @OA\Property(property="paginationLinks", type="object",
     *                 @OA\Property(property="first", type="string",example="v1/v1/scheduler/settings?page=1&per-page=25"),
     *                 @OA\Property(property="previous", type="string",example="v1/v1/scheduler/settings?page=1&per-page=25"),
     *                 @OA\Property(property="self", type="string",example="v1/v1/scheduler/settings?page=1&per-page=25"),
     *                 @OA\Property(property="next", type="string",example="v1/v1/scheduler/settings?page=1&per-page=25"),
     *                 @OA\Property(property="last", type="string",example="v1/v1/scheduler/settings?page=1&per-page=25"),
     *             ),
     *         )
     *     )
     *   ),
     * )
     */
    'GET settings'         => 'settings/index',

    /**
     * @OA\Post(
     *     path="/scheduler/settings/create",
     *     summary="Creates a new Settings model ",
     *     tags={"Settings"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Fill in settings data",
     *         @OA\JsonContent(
     *             required={"id","start_time","end_time"},
     *             ref="#/components/schemas/Settings",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Data payload",
     *         @OA\JsonContent(
     *             @OA\Property(property="dataPayload", type="object",
     *                 @OA\Property(property="data", type="object",ref="#/components/schemas/Settings"),
     *                 @OA\Property(property="toastMessage", type="string", example="settings created successfully"),
     *                 @OA\Property(property="toastTheme", type="string",example="success"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Data Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errorPayload", type="object",
     *                 @OA\Property(property="errors", type="object", ref="#/components/schemas/Settings"),
     *                 @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *                 @OA\Property(property="toastTheme", type="string",example="danger"),
     *             )
     *         )
     *     )
     * )
     */
    'POST settings'         => 'settings/create',

    /**
     * @OA\Get(path="/scheduler/settings/{id}/view",
     *   summary="Displays a single Settings model",
     *   tags={"Settings"},
     *   @OA\Parameter(description="Settings unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *   @OA\Response(
     *     response=200,
     *     description="Displays a single Settings model.",
     *     @OA\JsonContent(
     *         @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/Settings")
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
    'GET settings/{id}'     => 'settings/view',

    /**
     * @OA\Put(
     *     path="/scheduler/settings/{id}/update",
     *     tags={"Settings"},
     *     summary="Updates an existing Settings model",
     *     @OA\Parameter(description="Settings unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Finds the Settings model to be updated based on its primary key value",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Settings",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Data payload",
     *         @OA\JsonContent(
     *             @OA\Property(property="dataPayload", type="object",
     *                 @OA\Property(property="data", type="object",ref="#/components/schemas/Settings"),
     *                 @OA\Property(property="toastMessage", type="string", example="settings updated successfully"),
     *                 @OA\Property(property="toastTheme", type="string",example="success"),
     *             )
     *         )
     *     ),
     * )
     */
    'PUT settings/{id}'     => 'settings/update',

    /**
     * @OA\Delete(path="/scheduler/settings/{id}/delete",
     *     tags={"Settings"},
     *     summary="Deletes an existing Settings model.",
     *     @OA\Parameter(description="Settings unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *     @OA\Response(
     *         response=202,
     *         description="Deletion successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="dataPayload", type="object")
     *         )
     *     ),
     * )
     */
    'DELETE settings/{id}'  => 'settings/delete',

    /**
     * @OA\Patch(path="/scheduler/settings/{id}/patch",
     *     tags={"Settings"},
     *     summary="Restores a deleted Settings model.",
     *     @OA\Parameter(description="Settings unique ID to restore",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *     @OA\Response(
     *         response=202,
     *         description="Restoration successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="dataPayload", type="object")
     *         )
     *     ),
     * )
     */
    'PATCH settings/{id}'  => 'settings/restore',
];
