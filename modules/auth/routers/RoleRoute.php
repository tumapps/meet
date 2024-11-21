<?php
 return [

     /**
     * @OA\Get(path="/auth/permission",
     *   summary="Lists all Permissions models ",
     *   tags={"Appointments"},
     *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
     *
      *    @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
     *     @OA\Parameter(description="User Id",in="query",name="_user_id", @OA\Schema(type="bigint")),
     *     
     *     
     *
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all auth/appointments",
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
    'GET roles'     => 'role/index',

     /**
     * @OA\Post(
     * path="/auth/role",
     * summary="Create a new role",
     * security={{}},
     * tags={"Roles"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Provide name & Type",
     *    @OA\JsonContent(
     *       required={"Name","Type"},
     *              @OA\Property(property="Name", type="string",title="Name", example="admin"),
     *              @OA\Property(property="Type", type="string",title="Type", example="admin"),
     *          
     *    ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="Data payload",
     *    @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *          @OA\Property(property="data", type="object",
     *              @OA\Property(property="Name", type="string",title="Name", example="admin"),
     *              @OA\Property(property="Type", type="string",title="Type", example="admin"),
     *          ),
     *          @OA\Property(property="toastMessage", type="string", example="Access Granted"),
     *          @OA\Property(property="toastTheme", type="string",example="success"),
     *       )
     *    )
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Data Validation Error",
     *    @OA\JsonContent(
     *       @OA\Property(property="errorPayload", type="object",
     *          @OA\Property(property="errors", type="object",
     *              @OA\Property(property="Name", type="string",title="Name", example="admin"),
     *              @OA\Property(property="Type", type="string",title="Type", example="admin"),
     *          ),
     *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *          @OA\Property(property="toastTheme", type="string",example="danger"),
     *       )
     *    )
     * )
     *),
     */
     'POST role'         => 'role/create',

     /**
     * @OA\Get(path="/auth/role/{name}",
     *   summary="Displays a single role model",
     *   tags={"role"},
     *   @OA\Parameter(description="role unique name to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *   @OA\Response(
     *     response=200,
     *     description="Displays a single role model.",
     *      @OA\JsonContent(
     *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/role"))
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
    'POST role-view'     => 'role/view',

    /**
     * @OA\Get(path="/auth/role/{name}",
     *   summary="Displays a single role model",
     *   tags={"role"},
     *   @OA\Parameter(description="role unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
     *   @OA\Response(
     *     response=200,
     *     description="Displays a single role model.",
     *      @OA\JsonContent(
     *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/role"))
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
    'GET get-role'     => 'role/get-role',

    /**
    * @OA\Put(
    *     path="/auth/role/{id}",
    *     tags={"Space"},
    *     summary="Updates an existing  model",
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
    'PUT role' => 'role/update',

    /**
    * @OA\Delete(path="/auth/role/{id}",
    *    tags={"Role"},
    *    summary="Deletes an existing Role model.",
    *     @OA\Parameter(description="Role unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
    *     @OA\Response(
    *         response=202,
    *         description="Deletion successful",
    *         @OA\JsonContent(
    *           @OA\Property(property="dataPayload", type="object")
    *         )
    *     ),
    * )
    */
    'DELETE role'  => 'role/delete',

    /**
    * @OA\Patch(path="/auth/role/{id}",
    *    tags={"Space"},
    *    summary="Restores a deleted role model.",
    *     @OA\Parameter(description="role unique ID to restore",in="path",name="id",required=true,@OA\Schema(type="string",)),
    *     @OA\Response(
    *         response=202,
    *         description="Restoration successful",
    *         @OA\JsonContent(
    *           @OA\Property(property="dataPayload", type="object")
    *         )
    *     ),
    * )
    */
    'PATCH role/{id}'  => 'role/delete',
    ];