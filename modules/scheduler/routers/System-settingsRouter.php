<?php
return [
//security={{}} #disable authorization on an endpoint
/**
 * @OA\Get(path="/scheduler/system-settings",
 *   summary="Lists all SystemSettings models ",
 *   tags={"SystemSettings"},
 *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
 *
  *    @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="App Name",in="query",name="_app_name", @OA\Schema(type="string")),
 *    @OA\Parameter(description="System Email",in="query",name="_system_email", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Category",in="query",name="_category", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Email Scheme",in="query",name="_email_scheme", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Email Smtps",in="query",name="_email_smtps", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Email Port",in="query",name="_email_port", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Email Encryption",in="query",name="_email_encryption", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Email Password",in="query",name="_email_password", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Description",in="query",name="_description", @OA\Schema(type="text")),
 *    @OA\Parameter(description="Email Username",in="query",name="_email_username", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Is Deleted",in="query",name="_is_deleted", @OA\Schema(type="boolean")),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Returns a data payload object for all scheduler/system-settings",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object",
 *              @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/SystemSettings")),
 *              @OA\Property(property="countOnPage", type="integer", example="25"),
 *              @OA\Property(property="totalCount", type="integer",example="50"),
 *              @OA\Property(property="perPage", type="integer",example="25"),
 *              @OA\Property(property="totalPages", type="integer",example="2"),
 *              @OA\Property(property="currentPage", type="integer",example="1"),
 *              @OA\Property(property="paginationLinks", type="object",
 *                  @OA\Property(property="first", type="string",example="v1/v1/scheduler/system-settings?page=1&per-page=25"),
 *                  @OA\Property(property="previous", type="string",example="v1/v1/scheduler/system-settings?page=1&per-page=25"),
 *                  @OA\Property(property="self", type="string",example="v1/v1/scheduler/system-settings?page=1&per-page=25"),
 *                  @OA\Property(property="next", type="string",example="v1/v1/scheduler/system-settings?page=1&per-page=25"),
 *                  @OA\Property(property="last", type="string",example="v1/v1/scheduler/system-settings?page=1&per-page=25"),
 *              ),
 *          )
 *      )
 *   ),
 * )
 */
'GET system-settings'         => 'system-settings/index',

/**
 * @OA\Post(
 * path="/scheduler/system-settings",
 * summary="Creates a new SystemSettings model ",
 * tags={"SystemSettings"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Fill in system-settings data",
 *    @OA\JsonContent(
 *       required={"id","system_email","email_scheme","email_smtps","email_port","email_encryption","email_password","email_username",},
 *       ref="#/components/schemas/SystemSettings",
 *    ),
 * ),
 * @OA\Response(
 *    response=201,
 *    description="Data payload",
 *    @OA\JsonContent(
 *       @OA\Property(property="dataPayload", type="object",
 *          @OA\Property(property="data", type="object",ref="#/components/schemas/SystemSettings"),
 *          @OA\Property(property="toastMessage", type="string", example="system-settings created succefully"),
 *          @OA\Property(property="toastTheme", type="string",example="success"),
 *       )
 *    )
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Data Validation Error",
 *    @OA\JsonContent(
 *       @OA\Property(property="errorPayload", type="object",
 *          @OA\Property(property="errors", type="object", ref="#/components/schemas/SystemSettings"),
 *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
 *          @OA\Property(property="toastTheme", type="string",example="danger"),
 *       )
 *    )
 * )
 *),
 */
'POST system-settings'         => 'system-settings/create',

/**
 * @OA\Get(path="/scheduler/system-settings/{id}",
 *   summary="Displays a single SystemSettings model",
 *   tags={"SystemSettings"},
 *   @OA\Parameter(description="SystemSettings unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
 *   @OA\Response(
 *     response=200,
 *     description="Displays a single SystemSettings model.",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/SystemSettings"))
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
'GET system-settings/{id}'     => 'system-settings/view',

/**
* @OA\Put(
*     path="/scheduler/system-settings/{id}",
*     tags={"SystemSettings"},
*     summary="Updates an existing SystemSettings model",
*     @OA\Parameter(description="SystemSettings unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\RequestBody(
*        required=true,
*        description="Finds the SystemSettings model to be updated based on its primary key value",
*        @OA\JsonContent(
*           ref="#/components/schemas/SystemSettings",
*        ),
*     ),
*    @OA\Response(
*       response=202,
*       description="Data payload",
*       @OA\JsonContent(
*          @OA\Property(property="dataPayload", type="object",
*             @OA\Property(property="data", type="object",ref="#/components/schemas/SystemSettings"),
*             @OA\Property(property="toastMessage", type="string", example="system-settings updated succefully"),
*             @OA\Property(property="toastTheme", type="string",example="success"),
*          )
*       )
*    ),
* )
*/
'PUT system-settings/{id}'     => 'system-settings/update',

/**
* @OA\Delete(path="/scheduler/system-settings/{id}",
*    tags={"SystemSettings"},
*    summary="Deletes an existing SystemSettings model.",
*     @OA\Parameter(description="SystemSettings unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Deletion successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'DELETE system-settings/{id}'  => 'system-settings/delete',

/**
* @OA\Patch(path="/scheduler/system-settings/{id}",
*    tags={"SystemSettings"},
*    summary="Restores a deleted SystemSettings model.",
*     @OA\Parameter(description="SystemSettings unique ID to restore",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Restoration successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'PATCH system-settings/{id}'  => 'system-settings/delete',
];