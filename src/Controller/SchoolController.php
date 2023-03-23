<?php

namespace App\Controller;

use OA\Get;
use Psr\Log\LoggerInterface;
use App\Repository\Connexion;
use OpenApi\Annotations as OA;
use App\Repository\SchoolRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class SchoolController extends ParentController
{

    protected $schoolRepository;

    public function __construct(
        SchoolRepository $rep, 
        LoggerInterface $logger, 
        Connexion $cnx)
    {

        // appel du parent Controller pour les fonctions et properties communes
        parent::__construct();

        $logger->info("SchoolController construct");

        //$logger->info('Hey ! I am writing in logs !!');
        //$logger->critical('Oops something bad is happening');

        $this->schoolRepository = $rep; // self
        $this->connexion = $cnx; // parent
        $this->schoolRepository->setConnexion($cnx);
        $this->logger = $logger; // parent
        //$this->custoResponse // parent
        $this->custoResponse->setobjectType("schools"); // files pour FileController
    }

/**
* @OA\Get(
*   path="/schools",
*   summary="Returns a list of schools",
*   tags={"Schools"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="getAllSchools",
*   @OA\Parameter(
*       name="opt",
*       in="query",
*       description="options",
*       required=false,
*       @OA\Schema(
*           type="string",
*       enum={"usedOnly","countOnly","followUp","full"}
*       )
*   ),
*   @OA\Response(
*       response=200,
*       description="OK",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(type="object", ref="#/components/schemas/Schools"),
*		        @OA\Schema(ref="#/components/schemas/Response")
* 	        }
*       )
*   ),
*   @OA\Response(
*       response=401,
*       description="UNAUTHORIZED",
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
    public function getAllSchools(Request $request): Response
    {
        // on recupere le token depuis le header
        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction (parent)
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at);

        if (strlen($sIsAuthorized)!==0) {
            return $this->returnNotAuthorized($sIsAuthorized);
        } else { // pas d'erreur

            // gestion des filtres
            $countOnly = (bool)$request->query->get('countOnly', 0);
            $usedOnly = (bool)$request->query->get('usedOnly', 0);
            $full = (bool)$request->query->get('full', 0); 
            $followUp = (bool)$request->query->get('followUp', 0); 

            if ($countOnly) {$this->logger->info("countOnly option");}
            if ($usedOnly) {$this->logger->info("usedOnly option");}
            if ($full) {$this->logger->info("full option");}
            if ($followUp) {$this->logger->info("followUp option");}
            
            if ($full) {
                // apiGetFull
                $schoolList = $this->schoolRepository->findAllSchoolsFull($this->connexion);
            } else if ($followUp) {
                // apiFollowUp
                $schoolList = $this->schoolRepository->schoolsFollowUp($this->connexion);
            } else {
                $schoolList = $this->schoolRepository->findAllSchools($this->connexion, $countOnly, $usedOnly);
            }


            $this->custoResponse->setData($schoolList);
            $this->custoResponse->setCount(count($schoolList));
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
* @OA\Get(
*   path="/schools/{id}",
*   tags={"Schools"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="getOneSchool",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="get by Id",
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
*		        @OA\Schema(type="object", ref="#/components/schemas/Schools"),
*		        @OA\Schema(ref="#/components/schemas/Response")
* 	        }
*       )
*   ),
*   @OA\Response(
*       response=401,
*       description="UNAUTHORIZED",
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
    public function getOneSchool(Request $request): Response
    {
        // on recupere le token depuis le header
        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction (parent)
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at);

        if (strlen($sIsAuthorized)!==0) {
            return $this->returnNotAuthorized($sIsAuthorized);
        } else { // pas d'erreur

            // attributs
            $id = $request->attributes->get('id', 0);
            $this->logger->info("id : $id");

            // gestion des filtres

            

            $schoolList = $this->schoolRepository->findOneSchool($this->connexion, $id);

            if (count($schoolList)>0) {

                // custoResponse cree dans Parent
                $this->custoResponse->setData($schoolList);
                $this->custoResponse->setId($id);
                $this->custoResponse->setCount(1);
                $jsonResponse = $this->custoResponse->getJsonResponse();

                $response = new JsonResponse(
                    $jsonResponse, 
                    Response::HTTP_OK, 
                    array(), 
                    false); // content, status, headers, false if already json

                return $response;
            } else {
                
                $this->custoResponse->setId($id);
                $this->custoResponse->setCount(0);
                $this->custoResponse->setStatusCode(Response::HTTP_NOT_FOUND);
                $this->custoResponse->setErrorDescription('No object found');
                $jsonResponse = $this->custoResponse->getJsonResponse();

                $response = new JsonResponse(
                    $jsonResponse, 
                    Response::HTTP_NOT_FOUND, 
                    array(), 
                    false); // content, status, headers, true if already json

                return $response;
            }
        }
    }
    
}
