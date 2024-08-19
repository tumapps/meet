<?php
return [
//security={{}} #disable authorization on an endpoint
/**
 * @OA\Get(path="/scheduler/availabilities",
 *   summary="Lists all Availability models ",
 *   tags={"Availability"},
 *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
 *
  *    @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="User Id",in="query",name="_user_id", @OA\Schema(type="bigint")),
 *    @OA\Parameter(description="Start Date",in="query",name="_start_date", @OA\Schema(type="date")),
 *    @OA\Parameter(description="End Date",in="query",name="_end_date", @OA\Schema(type="date")),
 *    @OA\Parameter(description="Start Time",in="query",name="_start_time", @OA\Schema(type="time")),
 *    @OA\Parameter(description="End Time",in="query",name="_end_time", @OA\Schema(type="time")),
 *    @OA\Parameter(description="Is Full Day",in="query",name="_is_full_day", @OA\Schema(type="boolean")),
 *    @OA\Parameter(description="Description",in="query",name="_description", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="timestamp")),
 *    @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="timestamp")),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Returns a data payload object for all scheduler/availabilities",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object",
 *              @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/Availability")),
 *              @OA\Property(property="countOnPage", type="integer", example="25"),
 *              @OA\Property(property="totalCount", type="integer",example="50"),
 *              @OA\Property(property="perPage", type="integer",example="25"),
 *              @OA\Property(property="totalPages", type="integer",example="2"),
 *              @OA\Property(property="currentPage", type="integer",example="1"),
 *              @OA\Property(property="paginationLinks", type="object",
 *                  @OA\Property(property="first", type="string",example="v1/v1/scheduler/availabilities?page=1&per-page=25"),
 *                  @OA\Property(property="previous", type="string",example="v1/v1/scheduler/availabilities?page=1&per-page=25"),
 *                  @OA\Property(property="self", type="string",example="v1/v1/scheduler/availabilities?page=1&per-page=25"),
 *                  @OA\Property(property="next", type="string",example="v1/v1/scheduler/availabilities?page=1&per-page=25"),
 *                  @OA\Property(property="last", type="string",example="v1/v1/scheduler/availabilities?page=1&per-page=25"),
 *              ),
 *          )
 *      )
 *   ),
 * )
 */
'GET availabilities'         => 'availability/index',

/**
 * @OA\Post(
 * path="/scheduler/availability",
 * summary="Creates a new Availability model ",
 * tags={"Availability"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Fill in availability data",
 *    @OA\JsonContent(
 *       required={"id","start_date","end_date",},
 *       ref="#/components/schemas/Availability",
 *    ),
 * ),
 * @OA\Response(
 *    response=201,
 *    description="Data payload",
 *    @OA\JsonContent(
 *       @OA\Property(property="dataPayload", type="object",
 *          @OA\Property(property="data", type="object",ref="#/components/schemas/Availability"),
 *          @OA\Property(property="toastMessage", type="string", example="availability created succefully"),
 *          @OA\Property(property="toastTheme", type="string",example="success"),
 *       )
 *    )
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Data Validation Error",
 *    @OA\JsonContent(
 *       @OA\Property(property="errorPayload", type="object",
 *          @OA\Property(property="errors", type="object", ref="#/components/schemas/Availability"),
 *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
 *          @OA\Property(property="toastTheme", type="string",example="danger"),
 *       )
 *    )
 * )
 *),
 */
'POST availability'         => 'availability/create',

/**
 * @OA\Get(path="/scheduler/availability/{id}",
 *   summary="Displays a single Availability model",
 *   tags={"Availability"},
 *   @OA\Parameter(description="Availability unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
 *   @OA\Response(
 *     response=200,
 *     description="Displays a single Availability model.",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/Availability"))
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
'GET availability/{id}'     => 'availability/view',

/**
* @OA\Put(
*     path="/scheduler/availability/{id}",
*     tags={"Availability"},
*     summary="Updates an existing Availability model",
*     @OA\Parameter(description="Availability unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\RequestBody(
*        required=true,
*        description="Finds the Availability model to be updated based on its primary key value",
*        @OA\JsonContent(
*           ref="#/components/schemas/Availability",
*        ),
*     ),
*    @OA\Response(
*       response=202,
*       description="Data payload",
*       @OA\JsonContent(
*          @OA\Property(property="dataPayload", type="object",
*             @OA\Property(property="data", type="object",ref="#/components/schemas/Availability"),
*             @OA\Property(property="toastMessage", type="string", example="availability updated succefully"),
*             @OA\Property(property="toastTheme", type="string",example="success"),
*          )
*       )
*    ),
* )
*/
'PUT availability/{id}'     => 'availability/update',

/**
* @OA\Delete(path="/scheduler/availability/{id}",
*    tags={"Availability"},
*    summary="Deletes an existing Availability model.",
*     @OA\Parameter(description="Availability unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Deletion successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'DELETE availability/{id}'  => 'availability/delete',

/**
* @OA\Patch(path="/scheduler/availability/{id}",
*    tags={"Availability"},
*    summary="Restores a deleted Availability model.",
*     @OA\Parameter(description="Availability unique ID to restore",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Restoration successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'PATCH availability/{id}'  => 'availability/delete',
];