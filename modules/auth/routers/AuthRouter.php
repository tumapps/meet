<?php
 return [

     /**
 * @OA\Get(path="/auth/auth",
 *   summary="Lists all Appointments models ",
 *   tags={"Appointments"},
 *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
 *
  *    @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="User Id",in="query",name="_user_id", @OA\Schema(type="bigint")),
 *    @OA\Parameter(description="Date",in="query",name="_date", @OA\Schema(type="date")),
 *    @OA\Parameter(description="Start Time",in="query",name="_start_time", @OA\Schema(type="time")),
 *    @OA\Parameter(description="End Time",in="query",name="_end_time", @OA\Schema(type="time")),
 *    @OA\Parameter(description="Contact Name",in="query",name="_contact_name", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Email Address",in="query",name="_email_address", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Mobile Number",in="query",name="_mobile_number", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Subject",in="query",name="_subject", @OA\Schema(type="text")),
 *    @OA\Parameter(description="Appointment Type",in="query",name="_appointment_type", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Status",in="query",name="_status", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="timestamp")),
 *    @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="timestamp")),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Returns a data payload object for all scheduler/appointments",
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
'GET users'     => 'auth/get-users',

     /**
     * @OA\Post(
     * path="/auth/login",
     * summary="Authenticates user based on the credentials provided",
     * security={{}},
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Provide Username & Password",
     *    @OA\JsonContent(
     *       required={"username","password"},
     *              @OA\Property(property="username", type="string",title="Username", example="admin"),
     *              @OA\Property(property="password", type="string",title="Password", example="admin"),
     *          
     *    ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="Data payload",
     *    @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *          @OA\Property(property="data", type="object",
     *              @OA\Property(property="username", type="string",title="Username", example="admin"),
     *              @OA\Property(property="password", type="string",title="Password", example="admin"),
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
     *              @OA\Property(property="username", type="string",title="Username", example="admin"),
     *              @OA\Property(property="password", type="string",title="Password", example="admin"),
     *          ),
     *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *          @OA\Property(property="toastTheme", type="string",example="danger"),
     *       )
     *    )
     * )
     *),
     */
     'POST login'         => 'auth/login',

    /**
     * @OA\Post(
     * path="/auth/register",
     * summary="Authenticates user based on the credentials provided",
     * security={{}},
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Provide Username & Password, confirm_password",
     *    @OA\JsonContent(
     *       required={"username","password", "comfirm_password"},
     *              @OA\Property(property="username", type="string",title="Username", example="admin"),
     *              @OA\Property(property="password", type="string",title="Password", example="admin"),
     *              @OA\Property(property="confirm_password", type="string",title="confirm password", example="admin"),
     *              
     *          
     *    ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="Data payload",
     *    @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *          @OA\Property(property="data", type="object",
     *              @OA\Property(property="username", type="string",title="Username", example="admin"),
     *              @OA\Property(property="password", type="string",title="Password", example="admin"),
     *              @OA\Property(property="confirm_password", type="string",title="confirm password", example="admin"),
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
     *              @OA\Property(property="username", type="string",title="Username", example="admin"),
     *              @OA\Property(property="password", type="string",title="Password", example="admin"),
     *              @OA\Property(property="confirm_password", type="string",title="confirm password", example="admin"),
     *          ),
     *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *          @OA\Property(property="toastTheme", type="string",example="danger"),
     *       )
     *    )
     * )
     *),
     */
     'POST register'         => 'auth/register',


     /**
     * @OA\Post(
     * path="/auth/password-reset-request",
     * summary="request for a password reset for user",
     * security={{}},
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Provide Username",
     *    @OA\JsonContent(
     *       required={"Username"},
     *              @OA\Property(property="username", type="string",title="Username", example="admin"),
     *    ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="Data payload",
     *    @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *          @OA\Property(property="data", type="object",
     *              @OA\Property(property="username", type="string",title="Username", example="admin"),
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
     *              @OA\Property(property="username", type="string",title="Username", example="admin"),
     *          ),
     *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *          @OA\Property(property="toastTheme", type="string",example="danger"),
     *       )
     *    )
     * )
     *),
     */
    'POST password-reset-request'  => 'auth/password-reset-request',


    /**
     * @OA\Post(
     * path="/auth/reset-password",
     * summary="resets password",
     * security={{}},
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Provide Password, Comfirm Password",
     *    @OA\JsonContent(
     *       required={"Password", "Comfirm Password"},
     *              @OA\Property(property="Password", type="string",title="Password", example="admin"),
     *              @OA\Property(property="ComfirmPassword", type="string",title="Comfirm Password", example="admin"),
     *    ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="Data payload",
     *    @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *          @OA\Property(property="data", type="object",
     *              @OA\Property(property="username", type="string",title="Username", example="admin"),
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
     *              @OA\Property(property="username", type="string",title="Username", example="admin"),
     *          ),
     *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
     *          @OA\Property(property="toastTheme", type="string",example="danger"),
     *       )
     *    )
     * )
     *),
     */
    'POST reset-password' => 'auth/reset-password',



    /**
     * @OA\Get(
     * path="/auth/me",
     * summary="Get the currently logged in user profile details",
     * tags={"Authentication"},
     * @OA\Response(
     *    response=201,
     *    description="Data payload",
     *    @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *          @OA\Property(property="data", type="object",
     *              @OA\Property(property="username", type="string",title="Username", example="admin"),
     *              @OA\Property(property="password", type="string",title="Password", example="admin"),
     *          ),
     *          @OA\Property(property="toastMessage", type="string", example="Access Granted"),
     *          @OA\Property(property="toastTheme", type="string",example="success"),
     *       )
     *    )
     * )
     *),
     */
    'GET profile-view'         => 'auth/me',

    /**
     * @OA\Get(
     * path="/auth/me",
     * summary="Updates user profile details",
     * tags={"Authentication"},
     * @OA\Response(
     *    response=201,
     *    description="Data payload",
     *    @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *          @OA\Property(property="data", type="object",
     *              @OA\Property(property="username", type="string",title="Username", example="admin"),
     *              @OA\Property(property="password", type="string",title="Password", example="admin"),
     *          ),
     *          @OA\Property(property="toastMessage", type="string", example="Access Granted"),
     *          @OA\Property(property="toastTheme", type="string",example="success"),
     *       )
     *    )
     * )
     *),
     */
    'PUT profile-update'         => 'auth/me',


    /**
     * @OA\Get(
     * path="/auth/refresh",
     * summary="Get the currently logged in user",
     * tags={"Authentication"},
     * @OA\Response(
     *    response=201,
     *    description="Data payload",
     *    @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *          @OA\Property(property="data", type="object",
     *              @OA\Property(property="token", type="string", example="xxx.xxx.xxx"),
     *          )
     *       )
     *    )
     * )
     *),
     */
    'POST refresh'         => 'auth/refresh',


    /**
     * @OA\DELETE(
     * path="/auth/refresh",
     * summary="log out currently logged in user",
     * tags={"Authentication"},
     * @OA\Response(
     *    response=201,
     *    description="Data payload",
     *    @OA\JsonContent(
     *       @OA\Property(property="dataPayload", type="object",
     *          @OA\Property(property="data", type="object",
     *              @OA\Property(property="token", type="string", example="xxx.xxx.xxx"),
     *          )
     *       )
     *    )
     * )
     *),
     */
     'DELETE refresh'     => 'auth/refresh'
    ];