<?php
return [
//security={{}} #disable authorization on an endpoint
/**
 * @OA\Get(path="/mainstream/databanks",
 *   summary="Lists all Databanks models ",
 *   tags={"Databanks"},
 *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
 *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
 *
  *    @OA\Parameter(description="Databank Id",in="query",name="_databank_id", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Databank Name",in="query",name="_databank_name", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Category",in="query",name="_category", @OA\Schema(type="string")),
 *    @OA\Parameter(description="Security Key",in="query",name="_security_key", @OA\Schema(type="text")),
 *    @OA\Parameter(description="Description",in="query",name="_description", @OA\Schema(type="text")),
 *    @OA\Parameter(description="Status",in="query",name="_status", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Is Deleted",in="query",name="_is_deleted", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="integer")),
 *    @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="integer")),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Returns a data payload object for all mainstream/databanks",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object",
 *              @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/Databanks")),
 *              @OA\Property(property="countOnPage", type="integer", example="25"),
 *              @OA\Property(property="totalCount", type="integer",example="50"),
 *              @OA\Property(property="perPage", type="integer",example="25"),
 *              @OA\Property(property="totalPages", type="integer",example="2"),
 *              @OA\Property(property="currentPage", type="integer",example="1"),
 *              @OA\Property(property="paginationLinks", type="object",
 *                  @OA\Property(property="first", type="string",example="v1/v1/mainstream/databanks?page=1&per-page=25"),
 *                  @OA\Property(property="previous", type="string",example="v1/v1/mainstream/databanks?page=1&per-page=25"),
 *                  @OA\Property(property="self", type="string",example="v1/v1/mainstream/databanks?page=1&per-page=25"),
 *                  @OA\Property(property="next", type="string",example="v1/v1/mainstream/databanks?page=1&per-page=25"),
 *                  @OA\Property(property="last", type="string",example="v1/v1/mainstream/databanks?page=1&per-page=25"),
 *              ),
 *          )
 *      )
 *   ),
 * )
 */
'GET databanks'         => 'databanks/index',

/**
 * @OA\Post(
 * path="/mainstream/databanks",
 * summary="Creates a new Databanks model ",
 * tags={"Databanks"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Fill in databanks data",
 *    @OA\JsonContent(
 *       required={"databank_id","databank_name","category","security_key","created_at","updated_at",},
 *       ref="#/components/schemas/Databanks",
 *    ),
 * ),
 * @OA\Response(
 *    response=201,
 *    description="Data payload",
 *    @OA\JsonContent(
 *       @OA\Property(property="dataPayload", type="object",
 *          @OA\Property(property="data", type="object",ref="#/components/schemas/Databanks"),
 *          @OA\Property(property="toastMessage", type="string", example="databanks created succefully"),
 *          @OA\Property(property="toastTheme", type="string",example="success"),
 *       )
 *    )
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Data Validation Error",
 *    @OA\JsonContent(
 *       @OA\Property(property="errorPayload", type="object",
 *          @OA\Property(property="errors", type="object", ref="#/components/schemas/Databanks"),
 *          @OA\Property(property="toastMessage", type="string", example="Some data could not be validated"),
 *          @OA\Property(property="toastTheme", type="string",example="danger"),
 *       )
 *    )
 * )
 *),
 */
'POST databanks'         => 'databanks/create',

/**
 * @OA\Get(path="/mainstream/databanks/{id}",
 *   summary="Displays a single Databanks model",
 *   tags={"Databanks"},
 *   @OA\Parameter(description="Databanks unique ID to load",in="path",name="id",required=true,@OA\Schema(type="string",)),
 *   @OA\Response(
 *     response=200,
 *     description="Displays a single Databanks model.",
 *      @OA\JsonContent(
 *          @OA\Property(property="dataPayload", type="object", ref="#/components/schemas/Databanks"))
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
'GET databanks/{id}'     => 'databanks/view',

/**
* @OA\Put(
*     path="/mainstream/databanks/{id}",
*     tags={"Databanks"},
*     summary="Updates an existing Databanks model",
*     @OA\Parameter(description="Databanks unique ID to load and update",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\RequestBody(
*        required=true,
*        description="Finds the Databanks model to be updated based on its primary key value",
*        @OA\JsonContent(
*           ref="#/components/schemas/Databanks",
*        ),
*     ),
*    @OA\Response(
*       response=202,
*       description="Data payload",
*       @OA\JsonContent(
*          @OA\Property(property="dataPayload", type="object",
*             @OA\Property(property="data", type="object",ref="#/components/schemas/Databanks"),
*             @OA\Property(property="toastMessage", type="string", example="databanks updated succefully"),
*             @OA\Property(property="toastTheme", type="string",example="success"),
*          )
*       )
*    ),
* )
*/
'PUT databanks/{id}'     => 'databanks/update',

/**
* @OA\Delete(path="/mainstream/databanks/{id}",
*    tags={"Databanks"},
*    summary="Deletes an existing Databanks model.",
*     @OA\Parameter(description="Databanks unique ID to delete",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Deletion successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'DELETE databanks/{id}'  => 'databanks/delete',

/**
* @OA\Patch(path="/mainstream/databanks/{id}",
*    tags={"Databanks"},
*    summary="Restores a deleted Databanks model.",
*     @OA\Parameter(description="Databanks unique ID to restore",in="path",name="id",required=true,@OA\Schema(type="string",)),
*     @OA\Response(
*         response=202,
*         description="Restoration successful",
*         @OA\JsonContent(
*           @OA\Property(property="dataPayload", type="object")
*         )
*     ),
* )
*/
'PATCH databanks/{id}'  => 'databanks/delete',
];