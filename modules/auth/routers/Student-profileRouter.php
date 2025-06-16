<?php
return [
//security={{}} #disable authorization on an endpoint
/**
 * @OA\Get(path="/auth/student-profiles",
 *   summary="Lists all StudentProfile models ",
 *   tags={"StudentProfile"},
 *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
 *
  *    @OA\Parameter(description="Std Id",in="query",name="_std_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Reg Number",in="query",name="_reg_number", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Student Email",in="query",name="_student_email", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Fee Paid",in="query",name="_fee_paid", @OA\Schema(type="decimal")),
 *    @OA\Parameter(description="Total Fee",in="query",name="_total_fee", @OA\Schema(type="decimal")),
 *    @OA\Parameter(description="Photo",in="query",name="_photo", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Status",in="query",name="_status", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Class",in="query",name="_class", @OA\Schema(type="string")),
 *    @OA\Parameter(description="School",in="query",name="_school", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Department",in="query",name="_department", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Year Of Study",in="query",name="_year_of_study", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Is Deleted",in="query",name="_is_deleted", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="integer")),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Returns a data payload object for all auth/student-profiles",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object",
 *              @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/StudentProfile")),
 *              @OA\Property(property="countOnPage", type="integer", example="25"),
 *              @OA\Property(property="totalCount", type="integer",example="50"),
 *              @OA\Property(property="perPage", type="integer",example="25"),
 *              @OA\Property(property="totalPages", type="integer",example="2"),
 *              @OA\Property(property="currentPage", type="integer",example="1"),
 *              @OA\Property(property="paginationLinks", type="object",
 *                  @OA\Property(property="first", type="string",example="v1/v1/auth/student-profiles?page=1&per-page=25"),
 *                  @OA\Property(property="previous", type="string",example="v1/v1/auth/student-profiles?page=1&per-page=25"),
 *                  @OA\Property(property="self", type="string",example="v1/v1/auth/student-profiles?page=1&per-page=25"),
 *                  @OA\Property(property="next", type="string",example="v1/v1/auth/student-profiles?page=1&per-page=25"),
 *                  @OA\Property(property="last", type="string",example="v1/v1/auth/student-profiles?page=1&per-page=25"),
 *              ),
 *          )
 *      )
 *   ),
 * )
 */
'GET student-profiles'         => 'student-profile/index',

/**
 * @OA\Post(
 * path="/auth/student-profile",
 * summary="Creates a new StudentProfile model ",
 * tags={"StudentProfile"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Fill in student-profile data",
 *    @OA\JsonContent(
 *       required={"std_id","reg_number","student_email","fee_paid","total_fee","status","class","school","department","year_of_study",},
 *       ref="#/components/schemas/StudentProfile",
 *    ),
 * ),
 * @OA\Response(
 *    response=201,
 *    description="Data payload",
 *    @OA\JsonContent(
 *       @OA\Property(property="dataPayload", type="object",
 *          @OA\Property(property="data", type="object",ref="#/components/schemas/StudentProfile"),
 *          @OA\Property(property="toastMessage", type="string", example="student-profile created succefully"),
 *          @OA\Property(property="toastTheme", type="string",example="success"),
 *       )
 *    )
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Data Validation Error",
 *    @OA\JsonContent(
 *       @OA\Property(property="errorPayload", type="object",
 *          @OA\Property(property="errors", type="object", ref="#/components/schemas/StudentProfile"),
 *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
 *          @OA\Property(property="toastTheme", type="string",example="danger"),
 *       )
 *    )
 * )
 *),
 */
'POST student-profile'         => 'student-profile/create',

/**
 * @OA\Get(path="/auth/student-profile/{id}",
 *   summary="Displays a single StudentProfile model",
 *   tags={"StudentProfile"},
 *   @OA\Parameter(description="StudentProfile unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
 *   @OA\Response(
 *     response=200,
 *     description="Displays a single StudentProfile model.",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/StudentProfile"))
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
'GET student-profile/{id}'     => 'student-profile/view',

/**
* @OA\Put(
*     path="/auth/student-profile/{id}",
*     tags={"StudentProfile"},
*     summary="Updates an existing StudentProfile model",
*     @OA\Parameter(description="StudentProfile unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\RequestBody(
*        required=true,
*        description="Finds the StudentProfile model to be updated based on its primary key value",
*        @OA\JsonContent(
*           ref="#/components/schemas/StudentProfile",
*        ),
*     ),
*    @OA\Response(
*       response=202,
*       description="Data payload",
*       @OA\JsonContent(
*          @OA\Property(property="dataPayload", type="object",
*             @OA\Property(property="data", type="object",ref="#/components/schemas/StudentProfile"),
*             @OA\Property(property="toastMessage", type="string", example="student-profile updated succefully"),
*             @OA\Property(property="toastTheme", type="string",example="success"),
*          )
*       )
*    ),
* )
*/
'PUT student-profile/{id}'     => 'student-profile/update',

/**
* @OA\Delete(path="/auth/student-profile/{id}",
*    tags={"StudentProfile"},
*    summary="Deletes an existing StudentProfile model.",
*     @OA\Parameter(description="StudentProfile unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Deletion successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'DELETE student-profile/{id}'  => 'student-profile/delete',

/**
* @OA\Patch(path="/auth/student-profile/{id}",
*    tags={"StudentProfile"},
*    summary="Restores a deleted StudentProfile model.",
*     @OA\Parameter(description="StudentProfile unique ID to restore",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Restoration successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'PATCH student-profile/{id}'  => 'student-profile/delete',
];