<?php
return [
    /**
     * @OA\Get(
     *   path="/auth/auth",
     *   summary="Lists all Appointments models",
     *   tags={"Appointments"},
     *   @OA\Parameter(description="Page No.", in="query", name="page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Page Size", in="query", name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search", in="query", name="_search", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Id", in="query", name="_id", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="User Id", in="query", name="_user_id", @OA\Schema(type="bigint")),
     *   @OA\Parameter(description="Date", in="query", name="_date", @OA\Schema(type="date")),
     *   @OA\Parameter(description="Start Time", in="query", name="_start_time", @OA\Schema(type="time")),
     *   @OA\Parameter(description="End Time", in="query", name="_end_time", @OA\Schema(type="time")),
     *   @OA\Parameter(description="Contact Name", in="query", name="_contact_name", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Email Address", in="query", name="_email_address", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Mobile Number", in="query", name="_mobile_number", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Subject", in="query", name="_subject", @OA\Schema(type="text")),
     *   @OA\Parameter(description="Appointment Type", in="query", name="_appointment_type", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Status", in="query", name="_status", @OA\Schema(type="string")),
     *   @OA\Parameter(description="Created At", in="query", name="_created_at", @OA\Schema(type="timestamp")),
     *   @OA\Parameter(description="Updated At", in="query", name="_updated_at", @OA\Schema(type="timestamp")),
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all scheduler/appointments",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="countOnPage", type="integer", example="25"),
     *         @OA\Property(property="totalCount", type="integer", example="50"),
     *         @OA\Property(property="perPage", type="integer", example="25"),
     *         @OA\Property(property="totalPages", type="integer", example="2"),
     *         @OA\Property(property="currentPage", type="integer", example="1"),
     *         @OA\Property(property="paginationLinks", type="object",
     *           @OA\Property(property="first", type="string", example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *           @OA\Property(property="previous", type="string", example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *           @OA\Property(property="self", type="string", example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *           @OA\Property(property="next", type="string", example="v1/v1/scheduler/appointments?page=1&per-page=25"),
     *           @OA\Property(property="last", type="string", example="v1/v1/scheduler/appointments?page=1&per-page=25")
     *         )
     *       )
     *     )
     *   )
     * )
     */
    'GET users' => 'auth/index',

    /**
     * @OA\Get(
     *   path="/auth/user",
     *   summary="Get a specific user",
     *   tags={"Users"},
     *   @OA\Response(
     *     response=200,
     *     description="Returns a user object",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="data", type="object",
     *           @OA\Property(property="id", type="integer", example="1"),
     *           @OA\Property(property="username", type="string", example="admin")
     *         )
     *       )
     *     )
     *   )
     * )
     */
    'GET user' => 'auth/get-user',

    /**
     * @OA\Put(
     *   path="/auth/lock-account/{id}",
     *   summary="Lock or unlock a user account",
     *   tags={"Users"},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="User ID",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Account status toggled successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *         @OA\Property(property="message", type="string", example="Account locked/unlocked successfully")
     *       )
     *     )
     *   )
     * )
     */
    'PUT lock-account/{id}' => 'auth/toggle-account-status',

    /**
     * @OA\Post(
     *   path="/auth/login",
     *   summary="Authenticates user based on the credentials provided",
     *   tags={"Authentication"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Provide Username & Password",
     *     @OA\JsonContent(
     *       required={"username", "password"},
     *       @OA\Property(property="username", type="string", example="admin"),
     *       @OA\Property(property="password", type="string", example="admin")
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
     *         @OA\Property(property="toastMessage", type="string", example="Access Granted"),
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
    'POST login' => 'auth/login',

    /**
     * @OA\Post(
     *   path="/auth/register",
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
     *   path="/auth/password-reset-request",
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
     *   path="/auth/reset-password",
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
     *   path="/auth/update-password",
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
     *   path="/auth/me",
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
     *   path="/auth/me",
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
     * @OA\Put(
     *   path="/auth/update-user/{id}",
     *   summary="Update a specific user",
     *   tags={"Users"},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="User ID",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     description="Provide updated user details",
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
    'PUT update-user/{id}' => 'auth/update-user',

    /**
     * @OA\Post(
     *   path="/auth/refresh",
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
     *   path="/auth/refresh",
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
