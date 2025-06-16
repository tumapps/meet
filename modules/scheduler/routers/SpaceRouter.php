<?php
return [
//security={{}} #disable authorization on an endpoint
/**
 * @OA\Get(path="/scheduler/spaces",
 *   summary="Lists all Space models ",
 *   tags={"Space"},
 *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
 *
  *    @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Level Id",in="query",name="_level_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Name",in="query",name="_name", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Start Time",in="query",name="_opening_time", @OA\Schema(type="time")),
 *    @OA\Parameter(description="End Time",in="query",name="_closing_time", @OA\Schema(type="time")),
 *    @OA\Parameter(description="Is Locked",in="query",name="_is_locked", @OA\Schema(type="boolean")),
 *    @OA\Parameter(description="Location",in="query",name="_location", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Description",in="query",name="_description", @OA\Schema(type="text")),
 *    @OA\Parameter(description="Is Deleted",in="query",name="_is_deleted", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="integer")),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Returns a data payload object for all scheduler/spaces",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object",
 *              @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/Space")),
 *              @OA\Property(property="countOnPage", type="integer", example="25"),
 *              @OA\Property(property="totalCount", type="integer",example="50"),
 *              @OA\Property(property="perPage", type="integer",example="25"),
 *              @OA\Property(property="totalPages", type="integer",example="2"),
 *              @OA\Property(property="currentPage", type="integer",example="1"),
 *              @OA\Property(property="paginationLinks", type="object",
 *                  @OA\Property(property="first", type="string",example="v1/v1/scheduler/spaces?page=1&per-page=25"),
 *                  @OA\Property(property="previous", type="string",example="v1/v1/scheduler/spaces?page=1&per-page=25"),
 *                  @OA\Property(property="self", type="string",example="v1/v1/scheduler/spaces?page=1&per-page=25"),
 *                  @OA\Property(property="next", type="string",example="v1/v1/scheduler/spaces?page=1&per-page=25"),
 *                  @OA\Property(property="last", type="string",example="v1/v1/scheduler/spaces?page=1&per-page=25"),
 *              ),
 *          )
 *      )
 *   ),
 * )
 */
'GET spaces'         => 'space/index',

/**
 * @OA\Post(
 * path="/scheduler/space",
 * summary="Creates a new Space model ",
 * tags={"Space"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Fill in space data",
 *    @OA\JsonContent(
 *       required={"id","level_id","name","opening_time","closing_time","created_at","updated_at",},
 *       ref="#/components/schemas/Space",
 *    ),
 * ),
 * @OA\Response(
 *    response=201,
 *    description="Data payload",
 *    @OA\JsonContent(
 *       @OA\Property(property="dataPayload", type="object",
 *          @OA\Property(property="data", type="object",ref="#/components/schemas/Space"),
 *          @OA\Property(property="toastMessage", type="string", example="space created succefully"),
 *          @OA\Property(property="toastTheme", type="string",example="success"),
 *       )
 *    )
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Data Validation Error",
 *    @OA\JsonContent(
 *       @OA\Property(property="errorPayload", type="object",
 *          @OA\Property(property="errors", type="object", ref="#/components/schemas/Space"),
 *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
 *          @OA\Property(property="toastTheme", type="string",example="danger"),
 *       )
 *    )
 * )
 *),
 */
'POST space'         => 'space/create',

/**
 * @OA\Get(path="/scheduler/space/{id}",
 *   summary="Displays a single Space model",
 *   tags={"Space"},
 *   @OA\Parameter(description="Space unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
 *   @OA\Response(
 *     response=200,
 *     description="Displays a single Space model.",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/Space"))
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
'GET space/{id}'     => 'space/view',

/**
* @OA\Put(
*     path="/scheduler/space/{id}",
*     tags={"Space"},
*     summary="Updates an existing Space model",
*     @OA\Parameter(description="Space unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\RequestBody(
*        required=true,
*        description="Finds the Space model to be updated based on its primary key value",
*        @OA\JsonContent(
*           ref="#/components/schemas/Space",
*        ),
*     ),
*    @OA\Response(
*       response=202,
*       description="Data payload",
*       @OA\JsonContent(
*          @OA\Property(property="dataPayload", type="object",
*             @OA\Property(property="data", type="object",ref="#/components/schemas/Space"),
*             @OA\Property(property="toastMessage", type="string", example="space updated succefully"),
*             @OA\Property(property="toastTheme", type="string",example="success"),
*          )
*       )
*    ),
* )
*/
'PUT space/{id}'     => 'space/update',

/**
 * @OA\Put(path="/scheduler/lock-space/{id}",
 *   summary="Locks a space",
 *   tags={"Space"},
 *   @OA\Parameter(description="Space unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
 *   @OA\Response(
 *     response=200,
 *     description="Lock a space",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/Space"))
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
'PUT lock-space/{id}'    => 'space/lock-space',

/**
* @OA\Delete(path="/scheduler/space/{id}",
*    tags={"Space"},
*    summary="Deletes an existing Space model.",
*     @OA\Parameter(description="Space unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Deletion successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'DELETE space/{id}'  => 'space/delete',

/**
* @OA\Patch(path="/scheduler/space/{id}",
*    tags={"Space"},
*    summary="Restores a deleted Space model.",
*     @OA\Parameter(description="Space unique ID to restore",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Restoration successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'PATCH space/{id}'  => 'space/delete',
];