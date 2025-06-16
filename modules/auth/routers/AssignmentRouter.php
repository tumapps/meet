<?php
return [
  /**
   * @OA\Post(
   *   path="/auth/assign-permission",
   *   summary="Assign Permissions to Role",
   *   tags={"Assignment"},
   *   @OA\Parameter(
   *     description="Page Size",
   *     in="query",
   *     name="per-page",
   *     @OA\Schema(type="integer")
   *   ),
   *   @OA\Parameter(
   *     description="Search",
   *     in="query",
   *     name="_search",
   *     @OA\Schema(type="string")
   *   ),
   *   @OA\Response(
   *     response=200,
   *     description="Returns a data payload object for all assigned items",
   *     @OA\JsonContent(
   *       @OA\Property(
   *         property="dataPayload",
   *         type="object",
   *         @OA\Property(property="countOnPage", type="integer", example="25"),
   *         @OA\Property(property="totalCount", type="integer", example="50"),
   *         @OA\Property(property="perPage", type="integer", example="25"),
   *         @OA\Property(property="totalPages", type="integer", example="2"),
   *         @OA\Property(property="currentPage", type="integer", example="1"),
   *         @OA\Property(property="paginationLinks", type="object")
   *       )
   *     )
   *   )
   * )
   */
  'POST assign-permission' => 'assignment/permission-to-role',

  /**
   * @OA\Post(
   *   path="/auth/bulk-assign",
   *   summary="Bulk Assign Permissions to Roles",
   *   tags={"Assignment"},
   *   @OA\Parameter(
   *     description="Page Size",
   *     in="query",
   *     name="per-page",
   *     @OA\Schema(type="integer")
   *   ),
   *   @OA\Parameter(
   *     description="Search",
   *     in="query",
   *     name="_search",
   *     @OA\Schema(type="string")
   *   ),
   *   @OA\Response(
   *     response=200,
   *     description="Returns a data payload object for all auth/appointments",
   *     @OA\JsonContent(
   *       @OA\Property(
   *         property="dataPayload",
   *         type="object",
   *         @OA\Property(property="countOnPage", type="integer", example="25"),
   *         @OA\Property(property="totalCount", type="integer", example="50"),
   *         @OA\Property(property="perPage", type="integer", example="25"),
   *         @OA\Property(property="totalPages", type="integer", example="2"),
   *         @OA\Property(property="currentPage", type="integer", example="1"),
   *         @OA\Property(property="paginationLinks", type="object")
   *       )
   *     )
   *   )
   * )
   */
  'POST assign' => 'assignment/bulk-assign',

  /**
   * @OA\Get(
   *   path="/auth/manage-role",
   *   summary="Manage Roles",
   *   tags={"Assignment"},
   *   @OA\Parameter(
   *     description="Page Size",
   *     in="query",
   *     name="per-page",
   *     @OA\Schema(type="integer")
   *   ),
   *   @OA\Parameter(
   *     description="Search",
   *     in="query",
   *     name="_search",
   *     @OA\Schema(type="string")
   *   ),
   *   @OA\Response(
   *     response=200,
   *     description="Returns a data payload object for all auth/appointments",
   *     @OA\JsonContent(
   *       @OA\Property(
   *         property="dataPayload",
   *         type="object",
   *         @OA\Property(property="countOnPage", type="integer", example="25"),
   *         @OA\Property(property="totalCount", type="integer", example="50"),
   *         @OA\Property(property="perPage", type="integer", example="25"),
   *         @OA\Property(property="totalPages", type="integer", example="2"),
   *         @OA\Property(property="currentPage", type="integer", example="1"),
   *         @OA\Property(property="paginationLinks", type="object")
   *       )
   *     )
   *   )
   * )
   */
  'GET manage-role' => 'assignment/manage-role',

  /**
   * @OA\Get(
   *   path="/auth/manage-user-roles/{id}",
   *   summary="Manage User Roles",
   *   tags={"Assignment"},
   *   @OA\Parameter(
   *     description="User ID",
   *     in="path",
   *     name="id",
   *     required=true,
   *     @OA\Schema(type="integer")
   *   ),
   *   @OA\Response(
   *     response=200,
   *     description="Returns a data payload object for all auth/assignment roles",
   *     @OA\JsonContent(
   *       @OA\Property(
   *         property="dataPayload",
   *         type="object",
   *         @OA\Property(property="countOnPage", type="integer", example="25"),
   *         @OA\Property(property="totalCount", type="integer", example="50"),
   *         @OA\Property(property="perPage", type="integer", example="25"),
   *         @OA\Property(property="totalPages", type="integer", example="2"),
   *         @OA\Property(property="currentPage", type="integer", example="1"),
   *         @OA\Property(property="paginationLinks", type="object")
   *       )
   *     )
   *   )
   * )
   */
  'GET manage-user-roles/{id}' => 'assignment/manage-user-roles',

  /**
   * @OA\Post(
   *   path="/auth/assign-role",
   *   summary="Assign Role to another Role",
   *   tags={"Assignment"},
   *   @OA\Response(
   *     response=200,
   *     description="Returns a data payload object for all auth/appointments",
   *     @OA\JsonContent(
   *       @OA\Property(
   *         property="dataPayload",
   *         type="object",
   *         @OA\Property(property="countOnPage", type="integer", example="25"),
   *         @OA\Property(property="totalCount", type="integer", example="50"),
   *         @OA\Property(property="perPage", type="integer", example="25"),
   *         @OA\Property(property="totalPages", type="integer", example="2"),
   *         @OA\Property(property="currentPage", type="integer", example="1"),
   *         @OA\Property(property="paginationLinks", type="object")
   *       )
   *     )
   *   )
   * )
   */
  'POST assign-role' => 'assignment/role-to-role',

  /**
   * @OA\Get(
   *   path="/auth/sync-permissions",
   *   summary="Sync Permissions",
   *   tags={"Assignment"},
   *   @OA\Response(
   *     response=200,
   *     description="Returns a data payload object for all auth/appointments",
   *     @OA\JsonContent(
   *       @OA\Property(
   *         property="dataPayload",
   *         type="object",
   *         @OA\Property(property="countOnPage", type="integer", example="25"),
   *         @OA\Property(property="totalCount", type="integer", example="50"),
   *         @OA\Property(property="perPage", type="integer", example="25"),
   *         @OA\Property(property="totalPages", type="integer", example="2"),
   *         @OA\Property(property="currentPage", type="integer", example="1"),
   *         @OA\Property(property="paginationLinks", type="object")
   *       )
   *     )
   *   )
   * )
   */
  'GET sync-permissions' => 'assignment/sync-permissions',

  /**
   * @OA\Post(
   *   path="/auth/assign-role-user/{id}",
   *   summary="Assign Role to User",
   *   tags={"Assignment"},
   *   @OA\Parameter(
   *     description="User ID",
   *     in="path",
   *     name="id",
   *     required=true,
   *     @OA\Schema(type="integer")
   *   ),
   *   @OA\Response(
   *     response=200,
   *     description="Returns a data payload object for all auth/appointments",
   *     @OA\JsonContent(
   *       @OA\Property(
   *         property="dataPayload",
   *         type="object",
   *         @OA\Property(property="countOnPage", type="integer", example="25"),
   *         @OA\Property(property="totalCount", type="integer", example="50"),
   *         @OA\Property(property="perPage", type="integer", example="25"),
   *         @OA\Property(property="totalPages", type="integer", example="2"),
   *         @OA\Property(property="currentPage", type="integer", example="1"),
   *         @OA\Property(property="paginationLinks", type="object")
   *       )
   *     )
   *   )
   * )
   */
  'POST assign-role-user/{id}' => 'assignment/role-to-user',

  /**
   * @OA\Post(
   *   path="/auth/revoke-user-roles/{id}",
   *   summary="Revoke Roles from User",
   *   tags={"Assignment"},
   *   @OA\Parameter(
   *     description="User ID",
   *     in="path",
   *     name="id",
   *     required=true,
   *     @OA\Schema(type="integer")
   *   ),
   *   @OA\Response(
   *     response=200,
   *     description="Returns a data payload object for all auth/appointments",
   *     @OA\JsonContent(
   *       @OA\Property(
   *         property="dataPayload",
   *         type="object",
   *         @OA\Property(property="countOnPage", type="integer", example="25"),
   *         @OA\Property(property="totalCount", type="integer", example="50"),
   *         @OA\Property(property="perPage", type="integer", example="25"),
   *         @OA\Property(property="totalPages", type="integer", example="2"),
   *         @OA\Property(property="currentPage", type="integer", example="1"),
   *         @OA\Property(property="paginationLinks", type="object")
   *       )
   *     )
   *   )
   * )
   */
  'POST revoke-user-roles/{id}' => 'assignment/revoke-role',

  /**
   * @OA\Post(
   *   path="/auth/remove",
   *   summary="Remove Role or Permission from Parent Role",
   *   tags={"Assignment"},
   *   @OA\Response(
   *     response=200,
   *     description="Returns a data payload object for all auth/appointments",
   *     @OA\JsonContent(
   *       @OA\Property(
   *         property="dataPayload",
   *         type="object",
   *         @OA\Property(property="countOnPage", type="integer", example="25"),
   *         @OA\Property(property="totalCount", type="integer", example="50"),
   *         @OA\Property(property="perPage", type="integer", example="25"),
   *         @OA\Property(property="totalPages", type="integer", example="2"),
   *         @OA\Property(property="currentPage", type="integer", example="1"),
   *         @OA\Property(property="paginationLinks", type="object")
   *       )
   *     )
   *   )
   * )
   */
  'POST remove' => 'assignment/remove',

  /**
   * @OA\Get(
   *   path="/auth/assignments",
   *   summary="List All Assignments",
   *   tags={"Assignments"},
   *   @OA\Parameter(
   *     description="Page No.",
   *     in="query",
   *     name="page",
   *     @OA\Schema(type="integer")
   *   ),
   *   @OA\Response(
   *     response=200,
   *     description="Returns a data payload object for all auth/assignments",
   *     @OA\JsonContent(
   *       @OA\Property(
   *         property="dataPayload",
   *         type="object",
   *         @OA\Property(property="countOnPage", type="integer", example="25"),
   *         @OA\Property(property="totalCount", type="integer", example="50"),
   *         @OA\Property(property="perPage", type="integer", example="25"),
   *         @OA\Property(property="totalPages", type="integer", example="2"),
   *         @OA\Property(property="currentPage", type="integer", example="1"),
   *         @OA\Property(property="paginationLinks", type="object")
   *       )
   *     )
   *   )
   * )
   */
  'GET assignments' => 'assignment/list-assignments',

  'GET get-items/{id}' => 'assignment/get-items',
];
