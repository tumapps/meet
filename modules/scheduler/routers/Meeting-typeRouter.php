<?php
return [
//security={{}} #disable authorization on an endpoint
/**
 * @OA\Get(path="/scheduler/meeting-types",
 *   summary="Lists all MeetingTypes models ",
 *   tags={"MeetingTypes"},
 *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
 *
  *    @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Type",in="query",name="_type", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Is Deleted",in="query",name="_is_deleted", @OA\Schema(type="boolean")),
 *    @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="integer")),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Returns a data payload object for all scheduler/meeting-types",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object",
 *              @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/MeetingTypes")),
 *              @OA\Property(property="countOnPage", type="integer", example="25"),
 *              @OA\Property(property="totalCount", type="integer",example="50"),
 *              @OA\Property(property="perPage", type="integer",example="25"),
 *              @OA\Property(property="totalPages", type="integer",example="2"),
 *              @OA\Property(property="currentPage", type="integer",example="1"),
 *              @OA\Property(property="paginationLinks", type="object",
 *                  @OA\Property(property="first", type="string",example="v1/v1/scheduler/meeting-types?page=1&per-page=25"),
 *                  @OA\Property(property="previous", type="string",example="v1/v1/scheduler/meeting-types?page=1&per-page=25"),
 *                  @OA\Property(property="self", type="string",example="v1/v1/scheduler/meeting-types?page=1&per-page=25"),
 *                  @OA\Property(property="next", type="string",example="v1/v1/scheduler/meeting-types?page=1&per-page=25"),
 *                  @OA\Property(property="last", type="string",example="v1/v1/scheduler/meeting-types?page=1&per-page=25"),
 *              ),
 *          )
 *      )
 *   ),
 * )
 */
'GET meeting-types'         => 'meeting-type/index',

/**
 * @OA\Post(
 * path="/scheduler/meeting-type",
 * summary="Creates a new MeetingTypes model ",
 * tags={"MeetingTypes"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Fill in meeting-type data",
 *    @OA\JsonContent(
 *       required={"id","type",},
 *       ref="#/components/schemas/MeetingTypes",
 *    ),
 * ),
 * @OA\Response(
 *    response=201,
 *    description="Data payload",
 *    @OA\JsonContent(
 *       @OA\Property(property="dataPayload", type="object",
 *          @OA\Property(property="data", type="object",ref="#/components/schemas/MeetingTypes"),
 *          @OA\Property(property="toastMessage", type="string", example="meeting-type created succefully"),
 *          @OA\Property(property="toastTheme", type="string",example="success"),
 *       )
 *    )
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Data Validation Error",
 *    @OA\JsonContent(
 *       @OA\Property(property="errorPayload", type="object",
 *          @OA\Property(property="errors", type="object", ref="#/components/schemas/MeetingTypes"),
 *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
 *          @OA\Property(property="toastTheme", type="string",example="danger"),
 *       )
 *    )
 * )
 *),
 */
'POST meeting-type'         => 'meeting-type/create',

/**
 * @OA\Get(path="/scheduler/meeting-type/{id}",
 *   summary="Displays a single MeetingTypes model",
 *   tags={"MeetingTypes"},
 *   @OA\Parameter(description="MeetingTypes unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
 *   @OA\Response(
 *     response=200,
 *     description="Displays a single MeetingTypes model.",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/MeetingTypes"))
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
'GET meeting-type/{id}'     => 'meeting-type/view',

/**
* @OA\Put(
*     path="/scheduler/meeting-type/{id}",
*     tags={"MeetingTypes"},
*     summary="Updates an existing MeetingTypes model",
*     @OA\Parameter(description="MeetingTypes unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\RequestBody(
*        required=true,
*        description="Finds the MeetingTypes model to be updated based on its primary key value",
*        @OA\JsonContent(
*           ref="#/components/schemas/MeetingTypes",
*        ),
*     ),
*    @OA\Response(
*       response=202,
*       description="Data payload",
*       @OA\JsonContent(
*          @OA\Property(property="dataPayload", type="object",
*             @OA\Property(property="data", type="object",ref="#/components/schemas/MeetingTypes"),
*             @OA\Property(property="toastMessage", type="string", example="meeting-type updated succefully"),
*             @OA\Property(property="toastTheme", type="string",example="success"),
*          )
*       )
*    ),
* )
*/
'PUT meeting-type/{id}'     => 'meeting-type/update',

/**
* @OA\Delete(path="/scheduler/meeting-type/{id}",
*    tags={"MeetingTypes"},
*    summary="Deletes an existing MeetingTypes model.",
*     @OA\Parameter(description="MeetingTypes unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Deletion successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'DELETE meeting-type/{id}'  => 'meeting-type/delete',

/**
* @OA\Patch(path="/scheduler/meeting-type/{id}",
*    tags={"MeetingTypes"},
*    summary="Restores a deleted MeetingTypes model.",
*     @OA\Parameter(description="MeetingTypes unique ID to restore",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Restoration successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'PATCH meeting-type/{id}'  => 'meeting-type/delete',
];