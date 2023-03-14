<?php

namespace App\Controller;

use OpenApi\Annotations\Get;
use OpenApi\Annotations\Put;
use Psr\Log\LoggerInterface;
use App\Repository\Connexion;
use OpenApi\Annotations as OA;
use App\Manager\TeacherManager;
use OpenApi\Annotations\Delete;
use App\Repository\TeacherRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


class TeacherController extends ParentController
{
    protected $teacherRepository;

    public function __construct(
        TeacherRepository $rep,
        LoggerInterface $logger, 
        Connexion $cnx)
    {

        // appel du parent Controller pour les fonctions et properties communes
        parent::__construct();

        $logger->info("TeacherController construct");

        $this->teacherRepository = $rep; // self
        $this->connexion = $cnx; // parent
        $this->teacherRepository->setConnexion($cnx);
        $this->logger = $logger; // parent
        $this->custoResponse->setobjectType("teachers"); // files pour FileController
    }

/**
* @Get(
*   path="/teachers",
*   summary="Returns a list of danse teachers",
*   tags={"Teachers"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="teacherGetAll",
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
public function teacherGetAll(Request $request): Response {

    // on recupere le token depuis le header
    $at = $request->headers->get('access-token'); // string|null

    // recup du context par fonction (parent)
    $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

    $sIsAuthorized = $this->isApiAuthorized($context, $at);

    if (strlen($sIsAuthorized)!==0) {
        return $this->returnNotAuthorized($sIsAuthorized);
    } else { // pas d'erreur

        // enum={"followUp", "duplicates", "usedOnly"}
        $usedOnly = (bool)$request->query->get('usedOnly', 0);
        if ($usedOnly) {$this->logger->info("usedOnly option");}
        $followUp = (bool)$request->query->get('followUp', 0);
        if ($followUp) {$this->logger->info("followUp option");}
        $duplicates = (bool)$request->query->get('duplicates', 0);
        if ($duplicates) {$this->logger->info("duplicates option");}

        $id = (int)$request->attributes->get('id', 0);
        if($id !==0) {
            $this->logger->info("id=$id");
            $this->custoResponse->setId($id);
        }

        if ($followUp) {
            $teacherList = $this->teacherRepository->apiGetFollowUp();
        } else if ($duplicates) {
            $teacherList = $this->teacherRepository->apiDuplicates();
        } else {
            $teacherList = $this->teacherRepository->apiGetList($id, $usedOnly);
        }

        $this->custoResponse->setData($teacherList);
        $this->custoResponse->setCount(count($teacherList));
        $jsonResponse = $this->custoResponse->getJsonResponse();

        $response = new JsonResponse(
            $jsonResponse, 
            Response::HTTP_OK, 
            array(), 
            false); // content, status, headers, false if already json

        return $response;
    }
    
}



/**
* @Get(
*   path="/teachers/{id}",
*   summary="Returns a danse teacher identified by its Id",
*   tags={"Teachers"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="teacherGetById",
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
public function teacherGetById(Request $request): Response {
    // nothing
    return new Response;
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
*   operationId="teachersPut",
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
public function teachersPut(Request $request): Response {

    // on recupere le token depuis le header
    $at = $request->headers->get('access-token'); // string|null

    // recup du context par fonction (parent)
    $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

    $sIsAuthorized = $this->isApiAuthorized($context, $at);

    if (strlen($sIsAuthorized)!==0) {
        return $this->returnNotAuthorized($sIsAuthorized);
    } else {

        $arrayAccept=array('CND','VALCND','CNI','VALCNI','PHOTO','VALPHOTO','CARD','VALCARD','CNDPAID','COMMENT' );

        $id = (int)$request->attributes->get('id', 0);
        $type = $request->attributes->get('type', '');
        $value = $request->attributes->get('value', '');

        $this->logger->info("id: $id");
        $this->logger->info("type: $type");
        $this->logger->info("value: $value");

        $this->custoResponse->setId($id);
        $this->custoResponse->setValue($value);
        $this->custoResponse->setType($type);

        if (!in_array(strtoupper($type), $arrayAccept)) {

            $this->custoResponse->setErrorDescription("Operation Not Allowed");
            $this->custoResponse->setErrorType("Operation");
            $this->custoResponse->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);

            $jsonResponse = $this->custoResponse->getJsonResponse();

            $response = new JsonResponse(
                $jsonResponse, 
                Response::HTTP_METHOD_NOT_ALLOWED, 
                array(), 
                false); // content, status, headers, false if already json
            return $response;


        } else {

            $leType = $type;
            $leTypeOf = 'int';
            $isInputOK = true;
            switch ( strtoupper($type) ) {
                case 'CNDPAID':
                    $leType='pcnd';
                    $leTypeOf='string';
                    if (!is_numeric($value)) {
                        $isInputOK = false;
                    }
                    break;
                case 'CNI':
                case 'VALCNI':
                    $leType='valide';
                    if (!is_numeric($value)) {
                        $isInputOK = false;
                    }
                    break;
                case 'COMMENT':
                    $leTypeOf='string';
                    break;
                case 'PHOTO':
                case 'VALPHOTO':
                    $leType='photo';
                    if (!is_numeric($value)) {
                        $isInputOK = false;
                    }
                    break;
                case 'CARD':
                case 'VALCARD':
                    $leType='carte';
                    if (!is_numeric($value)) {
                        $isInputOK = false;
                    }
                    break;
                case 'CND':
                case 'VALCND':
                    $leType='cnd';
                    if (!is_numeric($value)) {
                        $isInputOK = false;
                    }
                    break; 
            }

            $this->logger->debug("leType: $leType");
            $this->logger->debug("leTypeOf: $leTypeOf");

            if ($isInputOK === false) {
                $this->custoResponse->setErrorDescription("$type has not a numeric value !");
                $this->custoResponse->setErrorType("Wrong type value");
                $this->custoResponse->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
            } else {

                $teacherStatus = $this->teacherRepository->teachersPut($id, $leType, $value, $leTypeOf);

                if ($teacherStatus === -1) {
                    $this->custoResponse->setErrorDescription($this->connexion->getBdd()->errorInfo());
                    $this->custoResponse->setErrorType("database error");
                    $this->custoResponse->setStatusCode(Response::HTTP_NOT_MODIFIED);
                } else {
                    $this->custoResponse->setCount($teacherStatus);
                    $this->custoResponse->setStatusCode(Response::HTTP_OK);
                }
            }
        }
        $jsonResponse = $this->custoResponse->getJsonResponse();

        $response = new JsonResponse(
            $jsonResponse, 
            $this->custoResponse->getStatusCode(), 
            array(), 
            false); // content, status, headers, false if already json
        return $response;


    }

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
*   operationId="teachersDelete",
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
    public function teachersDelete(Request $request): Response {
        
    // on recupere le token depuis le header
    $at = $request->headers->get('access-token'); // string|null

    // recup du context par fonction (parent)
    $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

    $sIsAuthorized = $this->isApiAuthorized($context, $at);

    if (strlen($sIsAuthorized)!==0) {
        return $this->returnNotAuthorized($sIsAuthorized);
    } else {

        $id = (int)$request->attributes->get('id', 0);
        $this->logger->info("id: $id");
        $this->custoResponse->setId($id);

        $teacherStatus = $this->teacherRepository->teachersDelete($id);
        if ($teacherStatus === false) {
            //$desc = $this->_bdd->errorInfo(); // array
            $this->custoResponse->setErrorDescription("unknown");
            $this->custoResponse->setErrorType('database error');
            $this->custoResponse->setStatusCode(304); 
        } else {
            $this->custoResponse->setStatusCode(Response::HTTP_OK);
            $this->custoResponse->setCount(1);
        }
        $jsonResponse = $this->custoResponse->getJsonResponse();

        $response = new JsonResponse(
            $jsonResponse, 
            $this->custoResponse->getStatusCode(), 
            array(), 
            false); // content, status, headers, false if already json
        return $response;

    }
    }
}
