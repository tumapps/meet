<?php
return [
//security={{}} #disable authorization on an endpoint
/**
 * @OA\Get(path="/auth/issues",
 *   summary="Lists all Issues models ",
 *   tags={"Issues"},
 *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
 *
  *    @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Staff Id",in="query",name="_staff_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Student Id",in="query",name="_student_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Issue",in="query",name="_issue", @OA\Schema(type="text")),
 *    @OA\Parameter(description="Is Deleted",in="query",name="_is_deleted", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="integer")),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Returns a data payload object for all auth/issues",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object",
 *              @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/Issues")),
 *              @OA\Property(property="countOnPage", type="integer", example="25"),
 *              @OA\Property(property="totalCount", type="integer",example="50"),
 *              @OA\Property(property="perPage", type="integer",example="25"),
 *              @OA\Property(property="totalPages", type="integer",example="2"),
 *              @OA\Property(property="currentPage", type="integer",example="1"),
 *              @OA\Property(property="paginationLinks", type="object",
 *                  @OA\Property(property="first", type="string",example="v1/v1/auth/issues?page=1&per-page=25"),
 *                  @OA\Property(property="previous", type="string",example="v1/v1/auth/issues?page=1&per-page=25"),
 *                  @OA\Property(property="self", type="string",example="v1/v1/auth/issues?page=1&per-page=25"),
 *                  @OA\Property(property="next", type="string",example="v1/v1/auth/issues?page=1&per-page=25"),
 *                  @OA\Property(property="last", type="string",example="v1/v1/auth/issues?page=1&per-page=25"),
 *              ),
 *          )
 *      )
 *   ),
 * )
 */
'GET issues'         => 'issues/index',

/**
 * @OA\Post(
 * path="/auth/issues",
 * summary="Creates a new Issues model ",
 * tags={"Issues"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Fill in issues data",
 *    @OA\JsonContent(
 *       required={"id","staff_id","student_id","issue",},
 *       ref="#/components/schemas/Issues",
 *    ),
 * ),
 * @OA\Response(
 *    response=201,
 *    description="Data payload",
 *    @OA\JsonContent(
 *       @OA\Property(property="dataPayload", type="object",
 *          @OA\Property(property="data", type="object",ref="#/components/schemas/Issues"),
 *          @OA\Property(property="toastMessage", type="string", example="issues created succefully"),
 *          @OA\Property(property="toastTheme", type="string",example="success"),
 *       )
 *    )
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Data Validation Error",
 *    @OA\JsonContent(
 *       @OA\Property(property="errorPayload", type="object",
 *          @OA\Property(property="errors", type="object", ref="#/components/schemas/Issues"),
 *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
 *          @OA\Property(property="toastTheme", type="string",example="danger"),
 *       )
 *    )
 * )
 *),
 */
'POST issues'         => 'issues/create',

/**
 * @OA\Get(path="/auth/issues/{id}",
 *   summary="Displays a single Issues model",
 *   tags={"Issues"},
 *   @OA\Parameter(description="Issues unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
 *   @OA\Response(
 *     response=200,
 *     description="Displays a single Issues model.",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/Issues"))
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
'GET issues/{id}'     => 'issues/view',

/**
* @OA\Put(
*     path="/auth/issues/{id}",
*     tags={"Issues"},
*     summary="Updates an existing Issues model",
*     @OA\Parameter(description="Issues unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\RequestBody(
*        required=true,
*        description="Finds the Issues model to be updated based on its primary key value",
*        @OA\JsonContent(
*           ref="#/components/schemas/Issues",
*        ),
*     ),
*    @OA\Response(
*       response=202,
*       description="Data payload",
*       @OA\JsonContent(
*          @OA\Property(property="dataPayload", type="object",
*             @OA\Property(property="data", type="object",ref="#/components/schemas/Issues"),
*             @OA\Property(property="toastMessage", type="string", example="issues updated succefully"),
*             @OA\Property(property="toastTheme", type="string",example="success"),
*          )
*       )
*    ),
* )
*/
'PUT issues/{id}'     => 'issues/update',

/**
* @OA\Delete(path="/auth/issues/{id}",
*    tags={"Issues"},
*    summary="Deletes an existing Issues model.",
*     @OA\Parameter(description="Issues unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Deletion successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'DELETE issues/{id}'  => 'issues/delete',

/**
* @OA\Patch(path="/auth/issues/{id}",
*    tags={"Issues"},
*    summary="Restores a deleted Issues model.",
*     @OA\Parameter(description="Issues unique ID to restore",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Restoration successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'PATCH issues/{id}'  => 'issues/delete',
];