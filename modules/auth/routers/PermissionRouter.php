<?php
return [
    /**
     * @OA\Get(
     *   path="/auth/permission",
     *   summary="Lists all Permissions models",
     *   tags={"Permissions"},
     *   @OA\Parameter(description="Page No.", in="query", name="page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Page Size", in="query", name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search", in="query", name="_search", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Id", in="query", name="_id", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="User Id", in="query", name="_user_id", @OA\Schema(type="bigint")),
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all permissions",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="countOnPage", type="integer", example="25"),
     *         @OA\Property(property="totalCount", type="integer", example="50"),
     *         @OA\Property(property="perPage", type="integer", example="25"),
     *         @OA\Property(property="totalPages", type="integer", example="2"),
     *         @OA\Property(property="currentPage", type="integer", example="1"),
     *         @OA\Property(property="paginationLinks", type="object",
     *           @OA\Property(property="first", type="string", example="v1/v1/auth/permissions?page=1&per-page=25"),
     *           @OA\Property(property="previous", type="string", example="v1/v1/auth/permissions?page=1&per-page=25"),
     *           @OA\Property(property="self", type="string", example="v1/v1/auth/permissions?page=1&per-page=25"),
     *           @OA\Property(property="next", type="string", example="v1/v1/auth/permissions?page=1&per-page=25"),
     *           @OA\Property(property="last", type="string", example="v1/v1/auth/permissions?page=1&per-page=25")
     *         )
     *       )
     *     )
     *   )
     * )
     */
    'GET permissions' => 'permission/index',

    /**
     * @OA\Post(
     *   path="/auth/permission",
     *   summary="Create a new permission",
     *   tags={"Permissions"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Provide permission details",
     *     @OA\JsonContent(
     *       required={"name", "description"},
     *       @OA\Property(property="name", type="string", example="create-user"),
     *       @OA\Property(property="description", type="string", example="Permission to create a user")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Data payload",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="data", type="object",
     *           @OA\Property(property="name", type="string", example="create-user"),
     *           @OA\Property(property="description", type="string", example="Permission to create a user")
     *         ),
     *         @OA\Property(property="toastMessage", type="string", example="Permission created successfully"),
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
     *           @OA\Property(property="name", type="string", example="create-user"),
     *           @OA\Property(property="description", type="string", example="Permission to create a user")
     *         ),
     *         @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *         @OA\Property(property="toastTheme", type="string", example="danger")
     *       )
     *     )
     *   )
     * )
     */
    'POST permission' => 'permission/create',

    /**
     * @OA\Post(
     *   path="/auth/register/user",
     *   summary="Register a new user",
     *   tags={"Authentication"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Provide Username, Password, and Confirm Password",
     *     @OA\JsonContent(
     *       required={"username", "password", "confirm_password"},
     *       @OA\Property(property="username", type="string", example="admin"),
     *       @OA\Property(property="password", type="string", example="admin"),
     *       @OA\Property(property="confirm_password", type="string", example="admin")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Data payload",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="data", type="object",
     *           @OA\Property(property="username", type="string", example="admin")
     *         ),
     *         @OA\Property(property="toastMessage", type="string", example="User registered successfully"),
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
     *           @OA\Property(property="username", type="string", example="admin")
     *         ),
     *         @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *         @OA\Property(property="toastTheme", type="string", example="danger")
     *       )
     *     )
     *   )
     * )
     */
    'POST register' => 'auth/register',

    /**
     * @OA\Post(
     *   path="/auth/password-reset-request/password",
     *   summary="Request a password reset for a user",
     *   tags={"Authentication"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Provide Username",
     *     @OA\JsonContent(
     *       required={"username"},
     *       @OA\Property(property="username", type="string", example="admin")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Data payload",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="data", type="object",
     *           @OA\Property(property="username", type="string", example="admin")
     *         ),
     *         @OA\Property(property="toastMessage", type="string", example="Password reset request successful"),
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
     *           @OA\Property(property="username", type="string", example="admin")
     *         ),
     *         @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *         @OA\Property(property="toastTheme", type="string", example="danger")
     *       )
     *     )
     *   )
     * )
     */
    'POST password-reset-request' => 'auth/password-reset-request',

    /**
     * @OA\Post(
     *   path="/auth/reset-password/{username}",
     *   summary="Reset user password",
     *   tags={"Authentication"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Provide Password and Confirm Password",
     *     @OA\JsonContent(
     *       required={"password", "confirm_password"},
     *       @OA\Property(property="password", type="string", example="admin"),
     *       @OA\Property(property="confirm_password", type="string", example="admin")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Data payload",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="data", type="object",
     *           @OA\Property(property="username", type="string", example="admin")
     *         ),
     *         @OA\Property(property="toastMessage", type="string", example="Password reset successful"),
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
     *           @OA\Property(property="password", type="string", example="admin")
     *         ),
     *         @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *         @OA\Property(property="toastTheme", type="string", example="danger")
     *       )
     *     )
     *   )
     * )
     */
    'POST reset-password' => 'auth/reset-password',

    /**
     * @OA\Post(
     *   path="/auth/update-password/{username}",
     *   summary="Update user password",
     *   tags={"Authentication"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Provide Old Password and New Password",
     *     @OA\JsonContent(
     *       required={"old_password", "new_password"},
     *       @OA\Property(property="old_password", type="string", example="admin"),
     *       @OA\Property(property="new_password", type="string", example="newpassword")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Data payload",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="data", type="object",
     *           @OA\Property(property="username", type="string", example="admin")
     *         ),
     *         @OA\Property(property="toastMessage", type="string", example="Password updated successfully"),
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
     *           @OA\Property(property="old_password", type="string", example="admin")
     *         ),
     *         @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *         @OA\Property(property="toastTheme", type="string", example="danger")
     *       )
     *     )
     *   )
     * )
     */
    'POST update-password' => 'auth/change-password',

    /**
     * @OA\Get(
     *   path="/auth/me/me",
     *   summary="Get the currently logged-in user profile details",
     *   tags={"Authentication"},
     *   @OA\Response(
     *     response=200,
     *     description="Data payload",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="data", type="object",
     *           @OA\Property(property="username", type="string", example="admin")
     *         )
     *       )
     *     )
     *   )
     * )
     */
    'GET profile-view' => 'auth/me',

    /**
     * @OA\Put(
     *   path="/auth/me/update",
     *   summary="Update the currently logged-in user profile details",
     *   tags={"Authentication"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Provide updated profile details",
     *     @OA\JsonContent(
     *       @OA\Property(property="username", type="string", example="admin")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Data payload",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="data", type="object",
     *           @OA\Property(property="username", type="string", example="admin")
     *         )
     *       )
     *     )
     *   )
     * )
     */
    'PUT profile-update' => 'auth/me',

    /**
     * @OA\Post(
     *   path="/auth/refresh/refresh",
     *   summary="Refresh the authentication token",
     *   tags={"Authentication"},
     *   @OA\Response(
     *     response=200,
     *     description="Data payload",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="data", type="object",
     *           @OA\Property(property="token", type="string", example="xxx.xxx.xxx")
     *         )
     *       )
     *     )
     *   )
     * )
     */
    'POST refresh' => 'auth/refresh',

    /**
     * @OA\Delete(
     *   path="/auth/refresh/delete",
     *   summary="Log out the currently logged-in user",
     *   tags={"Authentication"},
     *   @OA\Response(
     *     response=200,
     *     description="Data payload",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="data", type="object",
     *           @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *       )
     *     )
     *   )
     * )
     */
    'DELETE refresh' => 'auth/refresh'
];
