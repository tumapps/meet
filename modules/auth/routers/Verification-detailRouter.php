<?php
return [
//security={{}} #disable authorization on an endpoint
/**
 * @OA\Get(path="/auth/verification-details",
 *   summary="Lists all VerificationDetail models ",
 *   tags={"VerificationDetail"},
 *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
 *
  *    @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Student Id",in="query",name="_student_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Staff Id",in="query",name="_staff_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Is Deleted",in="query",name="_is_deleted", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="integer")),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Returns a data payload object for all auth/verification-details",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object",
 *              @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/VerificationDetail")),
 *              @OA\Property(property="countOnPage", type="integer", example="25"),
 *              @OA\Property(property="totalCount", type="integer",example="50"),
 *              @OA\Property(property="perPage", type="integer",example="25"),
 *              @OA\Property(property="totalPages", type="integer",example="2"),
 *              @OA\Property(property="currentPage", type="integer",example="1"),
 *              @OA\Property(property="paginationLinks", type="object",
 *                  @OA\Property(property="first", type="string",example="v1/v1/auth/verification-details?page=1&per-page=25"),
 *                  @OA\Property(property="previous", type="string",example="v1/v1/auth/verification-details?page=1&per-page=25"),
 *                  @OA\Property(property="self", type="string",example="v1/v1/auth/verification-details?page=1&per-page=25"),
 *                  @OA\Property(property="next", type="string",example="v1/v1/auth/verification-details?page=1&per-page=25"),
 *                  @OA\Property(property="last", type="string",example="v1/v1/auth/verification-details?page=1&per-page=25"),
 *              ),
 *          )
 *      )
 *   ),
 * )
 */
'GET verification-details'         => 'verification-detail/index',

/**
 * @OA\Post(
 * path="/auth/verification-detail",
 * summary="Creates a new VerificationDetail model ",
 * tags={"VerificationDetail"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Fill in verification-detail data",
 *    @OA\JsonContent(
 *       required={"id","student_id","staff_id",},
 *       ref="#/components/schemas/VerificationDetail",
 *    ),
 * ),
 * @OA\Response(
 *    response=201,
 *    description="Data payload",
 *    @OA\JsonContent(
 *       @OA\Property(property="dataPayload", type="object",
 *          @OA\Property(property="data", type="object",ref="#/components/schemas/VerificationDetail"),
 *          @OA\Property(property="toastMessage", type="string", example="verification-detail created succefully"),
 *          @OA\Property(property="toastTheme", type="string",example="success"),
 *       )
 *    )
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Data Validation Error",
 *    @OA\JsonContent(
 *       @OA\Property(property="errorPayload", type="object",
 *          @OA\Property(property="errors", type="object", ref="#/components/schemas/VerificationDetail"),
 *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
 *          @OA\Property(property="toastTheme", type="string",example="danger"),
 *       )
 *    )
 * )
 *),
 */
'POST verification-detail'         => 'verification-detail/create',

/**
 * @OA\Get(path="/auth/verification-detail/{id}",
 *   summary="Displays a single VerificationDetail model",
 *   tags={"VerificationDetail"},
 *   @OA\Parameter(description="VerificationDetail unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
 *   @OA\Response(
 *     response=200,
 *     description="Displays a single VerificationDetail model.",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/VerificationDetail"))
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
'GET verification-detail/{id}'     => 'verification-detail/view',

/**
* @OA\Put(
*     path="/auth/verification-detail/{id}",
*     tags={"VerificationDetail"},
*     summary="Updates an existing VerificationDetail model",
*     @OA\Parameter(description="VerificationDetail unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\RequestBody(
*        required=true,
*        description="Finds the VerificationDetail model to be updated based on its primary key value",
*        @OA\JsonContent(
*           ref="#/components/schemas/VerificationDetail",
*        ),
*     ),
*    @OA\Response(
*       response=202,
*       description="Data payload",
*       @OA\JsonContent(
*          @OA\Property(property="dataPayload", type="object",
*             @OA\Property(property="data", type="object",ref="#/components/schemas/VerificationDetail"),
*             @OA\Property(property="toastMessage", type="string", example="verification-detail updated succefully"),
*             @OA\Property(property="toastTheme", type="string",example="success"),
*          )
*       )
*    ),
* )
*/
'PUT verification-detail/{id}'     => 'verification-detail/update',

/**
* @OA\Delete(path="/auth/verification-detail/{id}",
*    tags={"VerificationDetail"},
*    summary="Deletes an existing VerificationDetail model.",
*     @OA\Parameter(description="VerificationDetail unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Deletion successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'DELETE verification-detail/{id}'  => 'verification-detail/delete',

/**
* @OA\Patch(path="/auth/verification-detail/{id}",
*    tags={"VerificationDetail"},
*    summary="Restores a deleted VerificationDetail model.",
*     @OA\Parameter(description="VerificationDetail unique ID to restore",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Restoration successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'PATCH verification-detail/{id}'  => 'verification-detail/delete',
];