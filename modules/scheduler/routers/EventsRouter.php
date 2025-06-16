<?php
return [
    //security={{}} #disable authorization on an endpoint

    /**
     * @OA\Get(path="/scheduler/events",
     *   summary="Lists all Events models ",
     *   tags={"Events"},
     *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
     *
     *    @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
     *    @OA\Parameter(description="Title",in="query",name="_title", @OA\Schema(type="string")),
     *    @OA\Parameter(description="Description",in="query",name="_description", @OA\Schema(type="text")),
     *    @OA\Parameter(description="Event Date",in="query",name="_event_date", @OA\Schema(type="date")),
     *    @OA\Parameter(description="Start Time",in="query",name="_start_time", @OA\Schema(type="time")),
     *    @OA\Parameter(description="End Time",in="query",name="_end_time", @OA\Schema(type="time")),
     *    @OA\Parameter(description="Is Deleted",in="query",name="_is_deleted", @OA\Schema(type="integer")),
     *    @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="integer")),
     *    @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="integer")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all scheduler/events",
     *      @OA\JsonContent(
     *          @OA\Property(property="dataPayload", type="object",
     *              @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/Events")),
     *              @OA\Property(property="countOnPage", type="integer", example="25"),
     *              @OA\Property(property="totalCount", type="integer",example="50"),
     *              @OA\Property(property="perPage", type="integer",example="25"),
     *              @OA\Property(property="totalPages", type="integer",example="2"),
     *              @OA\Property(property="currentPage", type="integer",example="1"),
     *              @OA\Property(property="paginationLinks", type="object",
     *                  @OA\Property(property="first", type="string",example="v1/v1/scheduler/events?page=1&per-page=25"),
     *                  @OA\Property(property="previous", type="string",example="v1/v1/scheduler/events?page=1&per-page=25"),
     *                  @OA\Property(property="self", type="string",example="v1/v1/scheduler/events?page=1&per-page=25"),
     *                  @OA\Property(property="next", type="string",example="v1/v1/scheduler/events?page=1&per-page=25"),
     *                  @OA\Property(property="last", type="string",example="v1/v1/scheduler/events?page=1&per-page=25"),
     *              ),
     *          )
     *      )
     *   ),
     * )
     */
    'GET events'         => 'events/index',

    /**
     * @OA\Post(
     * path="/scheduler/events",
     * summary="Creates a new Events model ",
     * tags={"Events"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Fill in events data",
     *    @OA\JsonContent(
     *       required={"id","title","event_date","start_time","end_time","created_at","updated_at",},
     *       ref="#/components/schemas/Events",
     *    ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="Data payload",
     *    @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *          @OA\Property(property="data", type="object",ref="#/components/schemas/Events"),
     *          @OA\Property(property="toastMessage", type="string", example="events created succefully"),
     *          @OA\Property(property="toastTheme", type="string",example="success"),
     *       )
     *    )
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Data Validation Error",
     *    @OA\JsonContent(
     *       @OA\Property(property="errorPayload", type="object",
     *          @OA\Property(property="errors", type="object", ref="#/components/schemas/Events"),
     *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *          @OA\Property(property="toastTheme", type="string",example="danger"),
     *       )
     *    )
     * )
     *),
     */
    'POST events'         => 'events/create',

    /**
     * @OA\Get(path="/scheduler/events/{id}",
     *   summary="Displays a single Events model",
     *   tags={"Events"},
     *   @OA\Parameter(description="Events unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *   @OA\Response(
     *     response=200,
     *     description="Displays a single Events model.",
     *      @OA\JsonContent(
     *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/Events"))
     *      ),
     *   @OA\Response(
     *     response=404,
     *     description="Resource not found",
     *      @OA\JsonContent(
     *           @OA\Property(property="errorPayload", type="object",
     *               @OA\Property(property="statusCode", type="integer", example=404 ),
     *               @OA\Property(property="errorMessage", type="string", example="Not found" )
     *           )
     *      )
     *   ),
     * )
     */
    'GET events/{id}'     => 'events/view',

    /**
     * @OA\Put(
     *     path="/scheduler/events/{id}",
     *     tags={"Events"},
     *     summary="Updates an existing Events model",
     *     @OA\Parameter(description="Events unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *     @OA\RequestBody(
     *        required=true,
     *        description="Finds the Events model to be updated based on its primary key value",
     *        @OA\JsonContent(
     *           ref="#/components/schemas/Events",
     *        ),
     *     ),
     *    @OA\Response(
     *       response=202,
     *       description="Data payload",
     *       @OA\JsonContent(
     *          @OA\Property(property="dataPayload", type="object",
     *             @OA\Property(property="data", type="object",ref="#/components/schemas/Events"),
     *             @OA\Property(property="toastMessage", type="string", example="events updated succefully"),
     *             @OA\Property(property="toastTheme", type="string",example="success"),
     *          )
     *       )
     *    ),
     * )
     */
    'PUT events/{id}'     => 'events/update',

    /**
     * @OA\Put(
     *     path="/scheduler/events/{id}/cancel",
     *     tags={"Events"},
     *     summary="Cancel an existing Events model",
     *     @OA\Parameter(description="Events unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *     @OA\RequestBody(
     *        required=true,
     *        description="Finds the Events model to be updated based on its primary key value",
     *        @OA\JsonContent(
     *           ref="#/components/schemas/Events",
     *        ),
     *     ),
     *    @OA\Response(
     *       response=202,
     *       description="Data payload",
     *       @OA\JsonContent(
     *          @OA\Property(property="dataPayload", type="object",
     *             @OA\Property(property="data", type="object",ref="#/components/schemas/Events"),
     *             @OA\Property(property="toastMessage", type="string", example="events canceled succefully"),
     *             @OA\Property(property="toastTheme", type="string",example="success"),
     *          )
     *       )
     *    ),
     * )
     */
    'PUT cancel/{id}'     => 'events/cancel',

    /**
     * @OA\Delete(path="/scheduler/events/{id}",
     *    tags={"Events"},
     *    summary="Deletes an existing Event model.",
     *     @OA\Parameter(description="Event unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *     @OA\Response(
     *         response=202,
     *         description="Deletion successful",
     *         @OA\JsonContent(
     *           @OA\Property(property="dataPayload", type="object")
     *         )
     *     ),
     * )
     */
    'DELETE events/{id}'  => 'events/delete',

    /**
     * @OA\Patch(path="/scheduler/events/{id}",
     *    tags={"Events"},
     *    summary="Restores a deleted Events model.",
     *     @OA\Parameter(description="Events unique ID to restore",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *     @OA\Response(
     *         response=202,
     *         description="Restoration successful",
     *         @OA\JsonContent(
     *           @OA\Property(property="dataPayload", type="object")
     *         )
     *     ),
     * )
     */
    'PATCH events/{id}'  => 'events/restore',
];