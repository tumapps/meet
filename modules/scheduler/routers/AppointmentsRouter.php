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
 *
  *    @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="User Id",in="query",name="_user_id", @OA\Schema(type="bigint")),
 *    @OA\Parameter(description="Date",in="query",name="_date", @OA\Schema(type="date")),
 *    @OA\Parameter(description="Start Time",in="query",name="_start_time", @OA\Schema(type="time")),
 *    @OA\Parameter(description="End Time",in="query",name="_end_time", @OA\Schema(type="time")),
 *    @OA\Parameter(description="Contact Name",in="query",name="_contact_name", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Email Address",in="query",name="_email_address", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Mobile Number",in="query",name="_mobile_number", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Subject",in="query",name="_subject", @OA\Schema(type="text")),
 *    @OA\Parameter(description="Appointment Type",in="query",name="_appointment_type", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Status",in="query",name="_status", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="timestamp")),
 *    @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="timestamp")),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Returns a data payload object for all scheduler/appointments",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object",
 *              @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/Appointments")),
 *              @OA\Property(property="countOnPage", type="integer", example="25"),
 *              @OA\Property(property="totalCount", type="integer",example="50"),
 *              @OA\Property(property="perPage", type="integer",example="25"),
 *              @OA\Property(property="totalPages", type="integer",example="2"),
 *              @OA\Property(property="currentPage", type="integer",example="1"),
 *              @OA\Property(property="paginationLinks", type="object",
 *                  @OA\Property(property="first", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
 *                  @OA\Property(property="previous", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
 *                  @OA\Property(property="self", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
 *                  @OA\Property(property="next", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
 *                  @OA\Property(property="last", type="string",example="v1/v1/scheduler/appointments?page=1&per-page=25"),
 *              ),
 *          )
 *      )
 *   ),
 * )
 */
'GET appointments'     => 'appointments/index',

/**
 * @OA\Post(
 * path="/scheduler/get-slots",
 * summary="Get all available slots ",
 * tags={"Appointments"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Fill in appointments data",
 *    @OA\JsonContent(
 *       required={"user_id","date",},
 *       ref="#/components/schemas/Appointments",
 *    ),
 * ),
 * @OA\Response(
 *    response=201,
 *    description="Data payload",
 *    @OA\JsonContent(
 *       @OA\Property(property="dataPayload", type="object",
 *          @OA\Property(property="data", type="object",ref="#/components/schemas/Appointments"),
 *          @OA\Property(property="toastMessage", type="string", example="appointments created succefully"),
 *          @OA\Property(property="toastTheme", type="string",example="success"),
 *       )
 *    )
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Data Validation Error",
 *    @OA\JsonContent(
 *       @OA\Property(property="errorPayload", type="object",
 *          @OA\Property(property="errors", type="object", ref="#/components/schemas/Appointments"),
 *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
 *          @OA\Property(property="toastTheme", type="string",example="danger"),
 *       )
 *    )
 * )
 *),
 */
'POST get-slots'       => 'appointments/get-slots',


/**
 * @OA\Get(path="/scheduler/slot-suggestion/{id}",
 *   summary="Display the next available slots for a particular affected appointment",
 *   tags={"Appointments"},
 *   @OA\Parameter(description="Appointments unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
 *   @OA\Response(
 *     response=200,
 *     description="Displays a single Appointments model.",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/Appointments"))
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
'GET slot-suggestion/{id}'	   => 'appointments/suggest-available-slots',


'POST self-booking'		=> 'appointments/self-book',
'GET types'  => 'appointments/appointments-types',

/**
 * @OA\Post(
 * path="/scheduler/appointments",
 * summary="Creates a new Appointments model ",
 * tags={"Appointments"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Fill in appointments data",
 *    @OA\JsonContent(
 *       required={"id","date","email_address",},
 *       ref="#/components/schemas/Appointments",
 *    ),
 * ),
 * @OA\Response(
 *    response=201,
 *    description="Data payload",
 *    @OA\JsonContent(
 *       @OA\Property(property="dataPayload", type="object",
 *          @OA\Property(property="data", type="object",ref="#/components/schemas/Appointments"),
 *          @OA\Property(property="toastMessage", type="string", example="appointments created succefully"),
 *          @OA\Property(property="toastTheme", type="string",example="success"),
 *       )
 *    )
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Data Validation Error",
 *    @OA\JsonContent(
 *       @OA\Property(property="errorPayload", type="object",
 *          @OA\Property(property="errors", type="object", ref="#/components/schemas/Appointments"),
 *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
 *          @OA\Property(property="toastTheme", type="string",example="danger"),
 *       )
 *    )
 * )
 *),
 */
'POST appointments'         => 'appointments/create',

/**
 * @OA\Get(path="/scheduler/appointments/{id}",
 *   summary="Displays a single Appointments model",
 *   tags={"Appointments"},
 *   @OA\Parameter(description="Appointments unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
 *   @OA\Response(
 *     response=200,
 *     description="Displays a single Appointments model.",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/Appointments"))
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
'GET appointments/{id}'     => 'appointments/view',

/**
* @OA\Put(
*     path="/scheduler/appointments/{id}",
*     tags={"Appointments"},
*     summary="Updates an existing Appointments model",
*     @OA\Parameter(description="Appointments unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\RequestBody(
*        required=true,
*        description="Finds the Appointments model to be updated based on its primary key value",
*        @OA\JsonContent(
*           ref="#/components/schemas/Appointments",
*        ),
*     ),
*    @OA\Response(
*       response=202,
*       description="Data payload",
*       @OA\JsonContent(
*          @OA\Property(property="dataPayload", type="object",
*             @OA\Property(property="data", type="object",ref="#/components/schemas/Appointments"),
*             @OA\Property(property="toastMessage", type="string", example="appointments updated succefully"),
*             @OA\Property(property="toastTheme", type="string",example="success"),
*          )
*       )
*    ),
* )
*/
'PUT appointments/{id}'     => 'appointments/update',

/**
* @OA\Put(
*     path="/scheduler/cancell/{id}",
*     tags={"Appointments"},
*     summary="Cancell an existing Appointments model",
*     @OA\Parameter(description="Appointments unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\RequestBody(
*        required=true,
*        description="Finds the Appointments model to be cancelled based on its primary key value",
*        @OA\JsonContent(
*           ref="#/components/schemas/Appointments",
*        ),
*     ),
*    @OA\Response(
*       response=202,
*       description="Data payload",
*       @OA\JsonContent(
*          @OA\Property(property="dataPayload", type="object",
*             @OA\Property(property="data", type="object",ref="#/components/schemas/Appointments"),
*             @OA\Property(property="toastMessage", type="string", example="appointments cancelled succefully"),
*             @OA\Property(property="toastTheme", type="string",example="success"),
*          )
*       )
*    ),
* )
*/
'PUT cancel/{id}'     => 'appointments/cancel',

/**
* @OA\Delete(path="/scheduler/appointments/{id}",
*    tags={"Appointments"},
*    summary="Deletes an existing Appointments model.",
*     @OA\Parameter(description="Appointments unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Deletion successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
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
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'PATCH appointments/{id}'  => 'appointments/delete',
];