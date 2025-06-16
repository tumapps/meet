<?php
return [
//security={{}} #disable authorization on an endpoint
/**
 * @OA\Get(path="/scheduler/space-availabilities",
 *   summary="Lists all SpaceAvailability models ",
 *   tags={"SpaceAvailability"},
 *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
 *
  *    @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Space Id",in="query",name="_space_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Date",in="query",name="_date", @OA\Schema(type="date")),
 *    @OA\Parameter(description="Start Time",in="query",name="_start_time", @OA\Schema(type="time")),
 *    @OA\Parameter(description="End Time",in="query",name="_end_time", @OA\Schema(type="time")),
 *    @OA\Parameter(description="Is Deleted",in="query",name="_is_deleted", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="integer")),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Returns a data payload object for all scheduler/space-availabilities",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object",
 *              @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/SpaceAvailability")),
 *              @OA\Property(property="countOnPage", type="integer", example="25"),
 *              @OA\Property(property="totalCount", type="integer",example="50"),
 *              @OA\Property(property="perPage", type="integer",example="25"),
 *              @OA\Property(property="totalPages", type="integer",example="2"),
 *              @OA\Property(property="currentPage", type="integer",example="1"),
 *              @OA\Property(property="paginationLinks", type="object",
 *                  @OA\Property(property="first", type="string",example="v1/v1/scheduler/space-availabilities?page=1&per-page=25"),
 *                  @OA\Property(property="previous", type="string",example="v1/v1/scheduler/space-availabilities?page=1&per-page=25"),
 *                  @OA\Property(property="self", type="string",example="v1/v1/scheduler/space-availabilities?page=1&per-page=25"),
 *                  @OA\Property(property="next", type="string",example="v1/v1/scheduler/space-availabilities?page=1&per-page=25"),
 *                  @OA\Property(property="last", type="string",example="v1/v1/scheduler/space-availabilities?page=1&per-page=25"),
 *              ),
 *          )
 *      )
 *   ),
 * )
 */
'GET space-availabilities'         => 'space-availability/index',

/**
 * @OA\Post(
 * path="/scheduler/space-availability",
 * summary="Creates a new SpaceAvailability model ",
 * tags={"SpaceAvailability"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Fill in space-availability data",
 *    @OA\JsonContent(
 *       required={"id","space_id","date","start_time","end_time","created_at","updated_at",},
 *       ref="#/components/schemas/SpaceAvailability",
 *    ),
 * ),
 * @OA\Response(
 *    response=201,
 *    description="Data payload",
 *    @OA\JsonContent(
 *       @OA\Property(property="dataPayload", type="object",
 *          @OA\Property(property="data", type="object",ref="#/components/schemas/SpaceAvailability"),
 *          @OA\Property(property="toastMessage", type="string", example="space-availability created succefully"),
 *          @OA\Property(property="toastTheme", type="string",example="success"),
 *       )
 *    )
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Data Validation Error",
 *    @OA\JsonContent(
 *       @OA\Property(property="errorPayload", type="object",
 *          @OA\Property(property="errors", type="object", ref="#/components/schemas/SpaceAvailability"),
 *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
 *          @OA\Property(property="toastTheme", type="string",example="danger"),
 *       )
 *    )
 * )
 *),
 */
'POST space-availability'         => 'space-availability/create',

/**
 * @OA\Get(path="/scheduler/space-availability/{id}",
 *   summary="Displays a single SpaceAvailability model",
 *   tags={"SpaceAvailability"},
 *   @OA\Parameter(description="SpaceAvailability unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
 *   @OA\Response(
 *     response=200,
 *     description="Displays a single SpaceAvailability model.",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/SpaceAvailability"))
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
'GET space-availability/{id}'     => 'space-availability/view',

/**
* @OA\Put(
*     path="/scheduler/space-availability/{id}",
*     tags={"SpaceAvailability"},
*     summary="Updates an existing SpaceAvailability model",
*     @OA\Parameter(description="SpaceAvailability unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\RequestBody(
*        required=true,
*        description="Finds the SpaceAvailability model to be updated based on its primary key value",
*        @OA\JsonContent(
*           ref="#/components/schemas/SpaceAvailability",
*        ),
*     ),
*    @OA\Response(
*       response=202,
*       description="Data payload",
*       @OA\JsonContent(
*          @OA\Property(property="dataPayload", type="object",
*             @OA\Property(property="data", type="object",ref="#/components/schemas/SpaceAvailability"),
*             @OA\Property(property="toastMessage", type="string", example="space-availability updated succefully"),
*             @OA\Property(property="toastTheme", type="string",example="success"),
*          )
*       )
*    ),
* )
*/
'PUT space-availability/{id}'     => 'space-availability/update',

/**
* @OA\Delete(path="/scheduler/space-availability/{id}",
*    tags={"SpaceAvailability"},
*    summary="Deletes an existing SpaceAvailability model.",
*     @OA\Parameter(description="SpaceAvailability unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Deletion successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'DELETE space-availability/{id}'  => 'space-availability/delete',

/**
* @OA\Patch(path="/scheduler/space-availability/{id}",
*    tags={"SpaceAvailability"},
*    summary="Restores a deleted SpaceAvailability model.",
*     @OA\Parameter(description="SpaceAvailability unique ID to restore",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Restoration successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'PATCH space-availability/{id}'  => 'space-availability/delete',
];