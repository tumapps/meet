<?php
return [
    /**
     * @OA\Get(
     *   path="/auth/roles",
     *   summary="Lists all Roles models",
     *   tags={"Roles"},
     *   @OA\Parameter(description="Page No.", in="query", name="page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Page Size", in="query", name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search", in="query", name="_search", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Id", in="query", name="_id", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="User Id", in="query", name="_user_id", @OA\Schema(type="bigint")),
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all roles",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="countOnPage", type="integer", example="25"),
     *         @OA\Property(property="totalCount", type="integer", example="50"),
     *         @OA\Property(property="perPage", type="integer", example="25"),
     *         @OA\Property(property="totalPages", type="integer", example="2"),
     *         @OA\Property(property="currentPage", type="integer", example="1"),
     *         @OA\Property(property="paginationLinks", type="object",
     *           @OA\Property(property="first", type="string", example="v1/v1/auth/roles?page=1&per-page=25"),
     *           @OA\Property(property="previous", type="string", example="v1/v1/auth/roles?page=1&per-page=25"),
     *           @OA\Property(property="self", type="string", example="v1/v1/auth/roles?page=1&per-page=25"),
     *           @OA\Property(property="next", type="string", example="v1/v1/auth/roles?page=1&per-page=25"),
     *           @OA\Property(property="last", type="string", example="v1/v1/auth/roles?page=1&per-page=25")
     *         )
     *       )
     *     )
     *   )
     * )
     */
    'GET roles' => 'role/index',

    /**
     * @OA\Post(
     *   path="/auth/role",
     *   summary="Create a new role",
     *   tags={"Roles"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Provide name and type",
     *     @OA\JsonContent(
     *       required={"Name", "Type"},
     *       @OA\Property(property="Name", type="string", example="admin"),
     *       @OA\Property(property="Type", type="string", example="admin")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Data payload",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="data", type="object",
     *           @OA\Property(property="Name", type="string", example="admin"),
     *           @OA\Property(property="Type", type="string", example="admin")
     *         ),
     *         @OA\Property(property="toastMessage", type="string", example="Role created successfully"),
     *         @OA\Property(property="toastTheme", type="string", example="success")
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Data Validation Error",
     *     @OA\JsonContent(
     *       @OA\Property(property="errorPayload", type="object",
     *         @OA\Property(property="errors", type="object",
     *           @OA\Property(property="Name", type="string", example="admin"),
     *           @OA\Property(property="Type", type="string", example="admin")
     *         ),
     *         @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *         @OA\Property(property="toastTheme", type="string", example="danger")
     *       )
     *     )
     *   )
     * )
     */
    'POST role' => 'role/create',

    /**
     * @OA\Get(
     *   path="/auth/get-role/{role_name}",
     *   summary="Displays a single role model by ID",
     *   tags={"Roles"},
     *   @OA\Parameter(
     *     description="Role unique ID to load",
     *     in="path",
     *     name="role_name",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Displays a single role model",
     *     @OA\JsonContent(
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Resource not found",
     *     @OA\JsonContent(
     *       @OA\Property(property="errorPayload", type="object",
     *         @OA\Property(property="statusCode", type="integer", example=404),
     *         @OA\Property(property="errorMessage", type="string", example="Not found")
     *       )
     *     )
     *   )
     * )
     */
    'GET get-role/{role_name}' => 'role/get-role',

    /**
     * @OA\Put(
     *   path="/auth/role/{id}",
     *   summary="Updates an existing role model",
     *   tags={"Roles"},
     *   @OA\Parameter(
     *     description="Role unique ID to load and update",
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     description="Finds the role model to be updated based on its primary key value",
     *    
     *   ),
     *   @OA\Response(
     *     response=202,
     *     description="Data payload",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="toastMessage", type="string", example="Role updated successfully"),
     *         @OA\Property(property="toastTheme", type="string", example="success")
     *       )
     *     )
     *   )
     * )
     */
    'PUT role' => 'role/update',

    /**
     * @OA\Delete(
     *   path="/auth/role/{id}",
     *   summary="Deletes an existing role model",
     *   tags={"Roles"},
     *   @OA\Parameter(
     *     description="Role unique ID to delete",
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=202,
     *     description="Deletion successful",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object")
     *     )
     *   )
     * )
     */
    'DELETE role/{id}' => 'role/delete',

    /**
     * @OA\Patch(
     *   path="/auth/role/{id}",
     *   summary="Restores a deleted role model",
     *   tags={"Roles"},
     *   @OA\Parameter(
     *     description="Role unique ID to restore",
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=202,
     *     description="Restoration successful",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object")
     *     )
     *   )
     * )
     */
    'PATCH role/{id}' => 'role/restore'
];
