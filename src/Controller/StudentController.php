<?php

namespace App\Controller;


use OpenApi\Annotations\Get;
use OpenApi\Annotations\Put;
use OpenApi\Annotations\Delete;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use App\Repository\Connexion;
use App\Repository\StudentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class StudentController extends ParentController
{

    protected $at;
    protected $studentRepository;
    protected $usedOnly             = false;
    protected  $startDate;
    protected  $birthdayValidated    = false;
    protected  $photoValidated       = false;
    protected  $member               = false;
    protected  $boardMember          = false;
    protected  $allFollowUp          = false;
    protected  $boardMemberFollowUp  = false;
    protected $csv                   = false;
    protected $followUp              = false;
    protected $duplicates            = false;
    protected $countOnly             = false;

    public function __construct(
        StudentRepository $rep,
        LoggerInterface $logger, 
        Connexion $cnx)
    {

        // appel du parent Controller pour les fonctions et properties communes
        parent::__construct();

        $logger->info("StudentController construct");

        $this->studentRepository = $rep; // self
        $this->connexion = $cnx; // parent
        $this->studentRepository->setConnexion($cnx);
        $this->logger = $logger; // parent
        $this->custoResponse->setobjectType("students"); 

        // recup request
        // dans parent via getRequest()

        $this->at                   = $this->getRequest()->headers->get('access-token'); // string|null
        $this->usedOnly             = (bool)$this->getRequest()->query->get('usedOnly', 0);
        $this->countOnly             = (bool)$this->getRequest()->query->get('countOnly', 0);
        $this->birthdayValidated    = (bool)$this->getRequest()->query->get('birthdayValidated', 0);
        $this->photoValidated       = (bool)$this->getRequest()->query->get('photoValidated', 0);
        $this->member               = (bool)$this->getRequest()->query->get('member', 0);
        $this->boardMember          = (bool)$this->getRequest()->query->get('boardMember', 0);
        $this->allFollowUp          = (bool)$this->getRequest()->query->get('allFollowUp', 0);
        $this->followUp             = (bool)$this->getRequest()->query->get('followUp', 0);
        $this->duplicates           = (bool)$this->getRequest()->query->get('duplicates', 0);
        $this->boardMemberFollowUp  = (bool)$this->getRequest()->query->get('boardMemberFollowUp', 0);
        $this->csv                  = (bool)$this->getRequest()->query->get('csv', 0);
        $this->startDate            = $this->getRequest()->query->get('startDate', date('Y-m-d'));

        //dd($request);
        //$logger->debug("usedOnly= ".$request->query->get('usedOnly', 0));
    }

/**
* @Get(
*   path="/students/{id}",
*   summary="Returns a student identified by its Id",
*   tags={"Students"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="studentGetById",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="student id",
*       required=true,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Parameter(
*       name="opt",
*       in="query",
*       description="option",
*       required=false,
*       @OA\Schema(
*           type="string",
*           enum={"startDate"}
*       )
*   ),
*   @OA\Response(
*       response=200,
*       description="OK",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(type="object", ref="#/components/schemas/Students"),
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
    public function studentGetById(Request $request) : ?Response {

        // on recupere le token depuis le header
        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction (parent)
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at);

        if (strlen($sIsAuthorized)!==0) {
            return $this->returnNotAuthorized($sIsAuthorized);
        } else { // pas d'erreur

            $id = $request->attributes->get('id', 0);
            $studentList = $this->studentRepository->studentGetById($id, $this->startDate);
            $this->logger->debug("id= $id");
            $this->logger->debug("startDate= $this->startDate");

            $this->custoResponse->setData($studentList);
            $this->custoResponse->setCount(count($studentList));
            $this->custoResponse->setid($id);
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
*   path="/students",
*   summary="Returns a list of students",
*   tags={"Students"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="studentGet",
*
*   @OA\Parameter(
*       name="startDate",
*       in="query",
*       description="date calcul age CND",
*       required=false,
*       @OA\Schema(
*           type="string"
*       )
*   ),
*   @OA\Parameter(
*       name="opt",
*       in="query",
*       description="option",
*       required=false,
*       @OA\Schema(
*           type="string",
*           enum={"birthdayValidated", "photoValidated", "member", "boardMember", "usedOnly", "followUp", "allFollowUp", "boardMemberFollowUp", "duplicates", "csv"}
*       )
*   ),
*   @OA\Response(
*       response=200,
*       description="OK",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Students"),
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
    public function studentGet(Request $request) : ?Response {

                // on recupere le token depuis le header
        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction (parent)
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at);

        if (strlen($sIsAuthorized)!==0) {
            return $this->returnNotAuthorized($sIsAuthorized);
        } else { // pas d'erreur

            // MGR
            // apiGetAll, apiDuplicates, apiFollowUp, apiAllFollowUp, apiBoardMemberFollowUp, apiCsv

            if ($this->allFollowUp ) {
                $studentList = $this->studentRepository->apiGetAllFollowUp();//apiAllFollowUp
                $this->custoResponse->setCount(count($studentList));
            } else if ($this->followUp) {
                $studentList = $this->studentRepository->apiGetFollowUp();//apiAllFollowUp
                $this->custoResponse->setCount(count($studentList));
            } else if ($this->duplicates) {
                $studentList = $this->studentRepository->apiDuplicates();
                $this->custoResponse->setCount(count($studentList));
            } else if ($this->boardMemberFollowUp) {
                $studentList = $this->studentRepository->apiBoardMemberFollowUp();
                $this->custoResponse->setCount(count($studentList));
            } else if ($this->csv) {
                $rawData = $this->studentRepository->apiCsv($this->connexion->getLanguage(), $this->startDate);            
                return new Response(
                    $rawData,
                    Response::HTTP_OK,
                    ["Content-Type"=>"text/csv;charset=UTF-8", "Pragma"=>"no-cache","Cache-Control"=>"no-store, no-cache, must-revalidate, max-age=0"]
                );
            } else if ($this->countOnly) {
                $studentList = $this->studentRepository->studentGetAll($this->startDate, $this->birthdayValidated, $this->photoValidated, $this->member , $this->boardMember ,$this->usedOnly);
                $this->custoResponse->setCount(count($studentList));
                $studentList = [];
            } else {
                $studentList = $this->studentRepository->studentGetAll($this->startDate, $this->birthdayValidated, $this->photoValidated, $this->member , $this->boardMember ,$this->usedOnly);
                $this->custoResponse->setCount(count($studentList));
            }

        
            $this->custoResponse->setData($studentList);
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
* @Put(
*   path="/students/{id}/{type}/{value}",
*   summary="Updates a student identified by its Id, with the type and its new value",
*   tags={"Students"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="studentPut",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="student id",
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
*           enum={"valCni","valPhoto","valCard","valMinor","valMajor","cndPaid","indPaid","duoPaid","grpPaid","comment"}
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
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Response")
* 	        }
*       )
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
    public function studentPut(Request $request) : ?Response {

        // on recupere le token depuis le header
        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction (parent)
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at);

        if (strlen($sIsAuthorized)!==0) {
            return $this->returnNotAuthorized($sIsAuthorized);
        } else {

            $arrayAccept=array('VALCNI','VALPHOTO','VALCARD','VALMINOR','VALMAJOR','CNDPAID','INDPAID','DUOPAID','GRPPAID','COMMENT' );

            $id = (int)$request->attributes->get('id', 0);
            $type = $request->attributes->get('type', '');
            $value = $request->attributes->get('value', '');

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

                $iStatus = -1;
                $iStatus = $this->studentRepository->studentUpdate($id, strtoupper($type), $value);

                // 1013
                $this->custoResponse->setCount($iStatus);

                $jsonResponse = $this->custoResponse->getJsonResponse();

                $response = new JsonResponse(
                    $jsonResponse, 
                    Response::HTTP_OK, 
                    array(), 
                    false); // content, status, headers, false if already json

                return $response;
            }
        }

    }



/**
* @Delete(
*   path="/students/{id}",
*   tags={"Students"},
*   security={"bearer"},
*   operationId="studentDelete",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="student id",
*       required=true,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Response(
*       response=200,
*       description="OK",
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
    public function studentDelete(Request $request) : ?Response {
        // on recupere le token depuis le header
        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction (parent)
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at);

        if (strlen($sIsAuthorized)!==0) {
            return $this->returnNotAuthorized($sIsAuthorized);
        } else {
            $id = (int)$request->attributes->get('id', 0);
            $this->logger->info("studentDelete id: $id");
            $this->custoResponse->setId($id);

            $studentStatus = $this->studentRepository->studentDelete($id);
            $this->logger->info("studentStatus: $studentStatus");

            if ($studentStatus === false) {
                $this->logger->info("studentStatus: failed");
                $this->custoResponse->setErrorDescription("unknown");
                $this->custoResponse->setErrorType('database error');
                $this->custoResponse->setStatusCode(304); 
            } else {
                $this->logger->info("studentStatus: success");
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
