<?php
return [
    //security={{}} #disable authorization on an endpoint
    /**
     * @OA\Get(path="/scheduler/system-settings",
     *   summary="Lists all SystemSettings models ",
     *   tags={"SystemSettings"},
     *   @OA\Parameter(description="Page No.",in="query",name="page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Page Size",in="query",name="per-page", @OA\Schema(type="integer")),
     *   @OA\Parameter(description="Search",in="query",name="_search", @OA\Schema(type="string")),
     *
     *    @OA\Parameter(description="Id",in="query",name="_id", @OA\Schema(type="integer")),
     *    @OA\Parameter(description="App Name",in="query",name="_app_name", @OA\Schema(type="string")),
     *    @OA\Parameter(description="System Email",in="query",name="_system_email", @OA\Schema(type="string")),
     *    @OA\Parameter(description="Category",in="query",name="_category", @OA\Schema(type="string")),
     *    @OA\Parameter(description="Email Scheme",in="query",name="_email_scheme", @OA\Schema(type="string")),
     *    @OA\Parameter(description="Email Smtps",in="query",name="_email_smtps", @OA\Schema(type="string")),
     *    @OA\Parameter(description="Email Port",in="query",name="_email_port", @OA\Schema(type="integer")),
     *    @OA\Parameter(description="Email Encryption",in="query",name="_email_encryption", @OA\Schema(type="string")),
     *    @OA\Parameter(description="Email Password",in="query",name="_email_password", @OA\Schema(type="string")),
     *    @OA\Parameter(description="Description",in="query",name="_description", @OA\Schema(type="text")),
     *    @OA\Parameter(description="Email Username",in="query",name="_email_username", @OA\Schema(type="string")),
     *    @OA\Parameter(description="Updated At",in="query",name="_updated_at", @OA\Schema(type="integer")),
     *    @OA\Parameter(description="Created At",in="query",name="_created_at", @OA\Schema(type="integer")),
     *    @OA\Parameter(description="Is Deleted",in="query",name="_is_deleted", @OA\Schema(type="boolean")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Returns a data payload object for all scheduler/system-settings",
     *      @OA\JsonContent(
     *          @OA\Property(property="dataPayload", type="object",
     *              @OA\Property(property="countOnPage", type="integer", example="25"),
     *              @OA\Property(property="totalCount", type="integer",example="50"),
     *              @OA\Property(property="perPage", type="integer",example="25"),
     *              @OA\Property(property="totalPages", type="integer",example="2"),
     *              @OA\Property(property="currentPage", type="integer",example="1"),
     *              @OA\Property(property="paginationLinks", type="object",
     *                  @OA\Property(property="first", type="string",example="v1/v1/scheduler/system-settings?page=1&per-page=25"),
     *                  @OA\Property(property="previous", type="string",example="v1/v1/scheduler/system-settings?page=1&per-page=25"),
     *                  @OA\Property(property="self", type="string",example="v1/v1/scheduler/system-settings?page=1&per-page=25"),
     *                  @OA\Property(property="next", type="string",example="v1/v1/scheduler/system-settings?page=1&per-page=25"),
     *                  @OA\Property(property="last", type="string",example="v1/v1/scheduler/system-settings?page=1&per-page=25"),
     *              ),
     *          )
     *      )
     *   ),
     * )
     */
    'GET docs'         => 'docs/json-docs',

];
