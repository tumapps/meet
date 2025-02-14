<?php
return [
//security={{}} #disable authorization on an endpoint
/**
 * @OA\Get(path="/scheduler/managed-users",
 *   summary="Lists all ManagedUsers models ",
 *   tags={"ManagedUsers"},
 *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
 *
  *    @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Secretary Id",in="query",name="_secretary_id", @OA\Schema(type="bigint")),
 *    @OA\Parameter(description="User Id",in="query",name="_user_id", @OA\Schema(type="bigint")),
 *    @OA\Parameter(description="Is Deleted",in="query",name="_is_deleted", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="integer")),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Returns a data payload object for all scheduler/managed-users",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object",
 *              @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/ManagedUsers")),
 *              @OA\Property(property="countOnPage", type="integer", example="25"),
 *              @OA\Property(property="totalCount", type="integer",example="50"),
 *              @OA\Property(property="perPage", type="integer",example="25"),
 *              @OA\Property(property="totalPages", type="integer",example="2"),
 *              @OA\Property(property="currentPage", type="integer",example="1"),
 *              @OA\Property(property="paginationLinks", type="object",
 *                  @OA\Property(property="first", type="string",example="v1/v1/scheduler/managed-users?page=1&per-page=25"),
 *                  @OA\Property(property="previous", type="string",example="v1/v1/scheduler/managed-users?page=1&per-page=25"),
 *                  @OA\Property(property="self", type="string",example="v1/v1/scheduler/managed-users?page=1&per-page=25"),
 *                  @OA\Property(property="next", type="string",example="v1/v1/scheduler/managed-users?page=1&per-page=25"),
 *                  @OA\Property(property="last", type="string",example="v1/v1/scheduler/managed-users?page=1&per-page=25"),
 *              ),
 *          )
 *      )
 *   ),
 * )
 */
'GET managed-users'         => 'managed-users/index',

/**
 * @OA\Post(
 * path="/scheduler/managed-users/{secretary_id}/{user_id}",
 * summary="Assign a user to the specified secretary id",
 * tags={"ManagedUsers"},
 * @OA\Parameter(name="secretary_id", in="path", required=true, description="Secretary Id", @OA\Schema(type="integer")),
 * @OA\Parameter(name="user_id", in="path", required=true, description="User Id", @OA\Schema(type="integer")),
 *
 * @OA\Response(
 *    response=201,
 *    description="Data payload",
 *    @OA\JsonContent(
 *       @OA\Property(property="dataPayload", type="object",
 *          @OA\Property(property="data", type="object",ref="#/components/schemas/ManagedUsers"),
 *          @OA\Property(property="toastMessage", type="string", example="managed-users created succefully"),
 *          @OA\Property(property="toastTheme", type="string",example="success"),
 *       )
 *    )
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Data Validation Error",
 *    @OA\JsonContent(
 *       @OA\Property(property="errorPayload", type="object",
 *          @OA\Property(property="errors", type="object", ref="#/components/schemas/ManagedUsers"),
 *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
 *          @OA\Property(property="toastTheme", type="string",example="danger"),
 *       )
 *    )
 * )
 *),
 */
'POST managed-users/{secretary_id}/{user_id}'         => 'managed-users/create',

/**
 * @OA\Get(path="/scheduler/managed-users/{id}",
 *   summary="Displays a single ManagedUsers model",
 *   tags={"ManagedUsers"},
 *   @OA\Parameter(description="ManagedUsers unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
 *   @OA\Response(
 *     response=200,
 *     description="Displays a single ManagedUsers model.",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/ManagedUsers"))
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
'GET managed-users/{id}'     => 'managed-users/view',

/**
* @OA\Put(
*     path="/scheduler/managed-users/{id}",
*     tags={"ManagedUsers"},
*     summary="Updates an existing ManagedUsers model",
*     @OA\Parameter(description="ManagedUsers unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\RequestBody(
*        required=true,
*        description="Finds the ManagedUsers model to be updated based on its primary key value",
*        @OA\JsonContent(
*           ref="#/components/schemas/ManagedUsers",
*        ),
*     ),
*    @OA\Response(
*       response=202,
*       description="Data payload",
*       @OA\JsonContent(
*          @OA\Property(property="dataPayload", type="object",
*             @OA\Property(property="data", type="object",ref="#/components/schemas/ManagedUsers"),
*             @OA\Property(property="toastMessage", type="string", example="managed-users updated succefully"),
*             @OA\Property(property="toastTheme", type="string",example="success"),
*          )
*       )
*    ),
* )
*/
'PUT managed-users/{id}'     => 'managed-users/update',

/**
* @OA\Delete(path="/scheduler/managed-users/{secretary_id}/{user_id}",
*    tags={"ManagedUsers"},
*    summary="Removes or reassing a user from beig managed by the specified secretary id.",
*     @OA\Parameter(description="ManagedUsers unique ID to reassing",in="path",name="secretary_id",required=true,@OA\Schema(type="integer",)),
*     @OA\Parameter(description="ManagedUsers unique ID to reassign",in="path",name="user_id",required=true,@OA\Schema(type="integer",)),
*     @OA\Response(
*         response=202,
*         description="Deletion successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'DELETE managed-users/{secretary_id}/{user_id}'  => 'managed-users/reassign',

/**
* @OA\Patch(path="/scheduler/managed-users/{id}",
*    tags={"ManagedUsers"},
*    summary="Restores a deleted ManagedUsers model.",
*     @OA\Parameter(description="ManagedUsers unique ID to restore",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Restoration successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'PATCH managed-users/{id}'  => 'managed-users/delete',
];