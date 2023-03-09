<?php

namespace App\Controller;

use OpenApi\Annotations\Put;
use OpenApi\Annotations\Delete;
use OpenApi\Annotations\Get;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;


class TeacherController extends ParentController
{

    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TeacherController.php',
        ]);
    }

/**
* @Get(
*   path="/teachers",
*   summary="Returns a list of danse teachers",
*   tags={"Teachers"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="teacher_get_all",
*
*   @OA\Parameter(
*       name="opt",
*       in="query",
*       description="option",
*       required=false,
*       @OA\Schema(
*           type="string",
*           enum={"followUp", "duplicates", "usedOnly"}
*       )
*   ),
*   @OA\Response(
*       response="200",
*       description="OK",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Teachers"),
*		        @OA\Schema(ref="#/components/schemas/Response")
* 	        }
*       )
*   ),
*   @OA\Response(
*       response="default",
*       description="Unexpected error",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Response")
* 	        }
*       )
*   )
* )
*/
public function teacher_get_all() {
    // nothing
}



/**
* @Get(
*   path="/teachers/{id}",
*   summary="Returns a danse teacher identified by its Id",
*   tags={"Teachers"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="teacher_get_byId",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="teacher id",
*       required=true,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Response(
*       response="200",
*       description="OK",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Teachers"),
*		        @OA\Schema(ref="#/components/schemas/Response")
* 	        }
*       )
*   ),
*   @OA\Response(
*       response="default",
*       description="Unexpected error",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Response")
* 	        }
*       )
*   )
* )
*/
public function teacher_get_byId() {
    // nothing
}

/* --------- PUT --------------- */

/**
* @Put(
*   path="/teachers/{id}/{type}/{value}",
*   summary="Updates a danse teacher identified by its Id, with the type and its new value",
*   tags={"Teachers"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="teachers_put",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="teacher id",
*       required=true,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Parameter(
*       name="type",
*       in="path",
*       description="operation type",
*       required=true,
*       @OA\Schema(
*           type="string",
*           enum={"cnd","cni","photo","card","cndPaid","comment"}
*       )
*   ),
*   @OA\Parameter(
*       name="value",
*       in="path",
*       description="operation value",
*       required=true,
*       @OA\Schema(
*           type="string"
*       )
*   ),
*   @OA\Response(
*       response=200,
*       description="OK",
*       @OA\JsonContent(ref="#/components/schemas/Response"),
*   ),
*   @OA\Response(
*       response=304,
*       description="Not Modified",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Response")
* 	        }
*       )
*   ),
*   @OA\Response(
*       response="default",
*       description="Unexpected error",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Response")
* 	        }
*       )
*   )
* )
*/
public function teachers_put() {
    // nothing
}

/* --------- DELETE --------------- */

/**
* @Delete(
*   path="/teachers/{id}",
*   summary="Removes a danse teacher identified by its Id",
*   tags={"Teachers"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="teachers_delete",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="teacher id",
*       required=true,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Response(
*       response=200,
*       description="OK",
*       @OA\JsonContent(ref="#/components/schemas/Response"),
*   ),
*   @OA\Response(
*       response="default",
*       description="Unexpected error",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Response")
* 	        }
*       )
*   )
* )
*/
    public function teachers_delete() {
        // nothing
    }
}
