<?php
 return [

     /**
     * @OA\Post(
     * path="/auth/auth/login",
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
     * path="/auth/auth/register",
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
     * path="/auth/password-rseset",
     * summary="resets user password",
     * security={{}},
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Provide Email",
     *    @OA\JsonContent(
     *       required={"Email"},
     *              @OA\Property(property="email", type="string",title="Email", example="admin@gmail.com"),
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
    'POST reset-password' => 'auth/reset-password',



    /**
     * @OA\Get(
     * path="/auth/auth/me",
     * summary="Get the currently logged in user",
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
    'GET auth/me'         => 'auth/me',


    /**
     * @OA\Get(
     * path="/auth/auth/refresh",
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
    'GET refresh'         => 'auth/refresh',


    /**
     * @OA\DELETE(
     * path="/auth/auth/refresh",
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
     'DELETE logout'     => 'auth/refresh'
    ];