<?php
 return [

     /**
     * @OA\Post(path="/auth/assignment",
     *   summary="Assign Permissions to Role",
     *   tags={"Assignment"},
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
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
     *              ),
     *          )
     *      )
     *   ),
     * )
     */
    'POST assign-permission'     => 'assignment/permission-to-role',

      /**
     * @OA\Post(path="/auth/assignment",
     *   summary="Assign Permissions to Role",
     *   tags={"Assignment"},
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
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
     *              ),
     *          )
     *      )
     *   ),
     * )
     */
    'POST assign'     => 'assignment/bulk-assign',
    

     /**
     * @OA\Get(path="/auth/manage-role",
     *   summary="Assign Permissions to Role",
     *   tags={"Assignment"},
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
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
     *              ),
     *          )
     *      )
     *   ),
     * )
     */
    // 'GET manage-role/{id}'     => 'assignment/manage-role',
    'GET manage-role' => 'assignment/manage-role',

    /**
     * @OA\Get(path="/auth/manage-user-roles",
     *   summary="returns a list of roles associated with a user and available roles that can be assigned",
     *   tags={"Assignment"},
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all auth/assignment roles",
     *      @OA\JsonContent(
     *          @OA\Property(property="dataPayload", type="object",
     *              @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/Appointments")),
     *              @OA\Property(property="countOnPage", type="integer", example="25"),
     *              @OA\Property(property="totalCount", type="integer",example="50"),
     *              @OA\Property(property="perPage", type="integer",example="25"),
     *              @OA\Property(property="totalPages", type="integer",example="2"),
     *              @OA\Property(property="currentPage", type="integer",example="1"),
     *              @OA\Property(property="paginationLinks", type="object",
     *              ),
     *          )
     *      )
     *   ),
     * )
     */
    'GET manage-user-roles/{id}'     => 'assignment/manage-user-roles',


    /**
     * @OA\Get(path="/auth/assignment",
     *   summary="Assign Role to another Role",
     *   tags={"Assignment"},
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
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
     *              ),
     *          )
     *      )
     *   ),
     * )
     */
    'POST assign-role'     => 'assignment/role-to-role',

     /**
     * @OA\Get(path="/auth/assignment",
     *   summary="sync all permission to roles and save in database",
     *   tags={"Assignment"},
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
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
     *              ),
     *          )
     *      )
     *   ),
     * )
     */
    'GET sync-permissions'     => 'assignment/sync-permissions',

     /**
     * @OA\Post(path="/auth/assignment",
     *   summary="Assign Permissions to Role",
     *   tags={"Assignment"},
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
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
     *              ),
     *          )
     *      )
     *   ),
     * )
     */
    'POST assign-role-user/{id}'     => 'assignment/role-to-user',

      /**
     * @OA\Post(path="/auth/assignment",
     *   summary="Assign Permissions to Role",
     *   tags={"Assignment"},
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
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
     *              ),
     *          )
     *      )
     *   ),
     * )
     */
    // 'POST assign-permission'     => 'assignment/permission-to-role',
    'POST revoke-user-roles/{id}'     => 'assignment/revoke-role',
     /**
     * @OA\Post(path="/auth/assignment",
     *   summary="Remove a role or permissions from a Parent role",
     *   tags={"Assignment"},
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
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
     *              ),
     *          )
     *      )
     *   ),
     * )
     */
    'POST remove'     => 'assignment/remove',

    /**
     * @OA\Get(path="/auth/assignments",
     *   summary="Lists all roles, permissions, and their assignments ",
     *   tags={"Assignments"},
     *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all auth/assigments",
     *      @OA\JsonContent(
     *          @OA\Property(property="dataPayload", type="object",
     *              @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/Assignment")),
     *              @OA\Property(property="countOnPage", type="integer", example="25"),
     *              @OA\Property(property="totalCount", type="integer",example="50"),
     *              @OA\Property(property="perPage", type="integer",example="25"),
     *              @OA\Property(property="totalPages", type="integer",example="2"),
     *              @OA\Property(property="currentPage", type="integer",example="1"),
     *              @OA\Property(property="paginationLinks", type="object",
     *                  @OA\Property(property="first", type="string",example="v1/v1/auth/levels?page=1&per-page=25"),
     *                  @OA\Property(property="previous", type="string",example="v1/v1/auth/levels?page=1&per-page=25"),
     *                  @OA\Property(property="self", type="string",example="v1/v1/auth/levels?page=1&per-page=25"),
     *                  @OA\Property(property="next", type="string",example="v1/v1/auth/levels?page=1&per-page=25"),
     *                  @OA\Property(property="last", type="string",example="v1/v1/auth/levels?page=1&per-page=25"),
     *              ),
     *          )
     *      )
     *   ),
     * )
     */
    'GET assignments' => 'assignment/list-assignments',

    'GET get-items/{id}' => 'assignment/get-items',
    ];