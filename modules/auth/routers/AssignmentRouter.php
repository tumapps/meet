<?php
 return [

     /**
     * @OA\Get(path="/auth/assignment",
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
    'POST assign-role-user'     => 'assignment/role-to-user',

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