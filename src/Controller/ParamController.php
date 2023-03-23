<?php

namespace App\Controller;

use OpenApi\Annotations\Get;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use App\Repository\Connexion;
use App\Repository\ParamRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ParamController extends ParentController
{
    protected $paramRepository;

    public function __construct(
        ParamRepository $rep, 
        LoggerInterface $logger, 
        Connexion $cnx)
    {
        // appel du parent Controller pour les fonctions et properties communes
        parent::__construct();

        $logger->info("ParamController construct");
        $this->paramRepository = $rep; // self
        $this->connexion = $cnx; // parent
        $this->paramRepository->setConnexion($cnx);
        $this->logger = $logger; // parent
        $this->custoResponse->setobjectType("params"); // files pour FileController
    }

/**
* @Get(
*   path="/params",
*   summary="Returns a list of the contest parameters",
*   tags={"Param"},
*   operationId="getAllParams",
*   @OA\Response(
*       response="200",
*       description="all params",
*       @OA\JsonContent(type="array", description="all parameters", @OA\Items(ref="#/components/schemas/Param")),
*   )
* )
*/
    public function getAllParams(Request $request): Response {
        $paramList = $this->paramRepository->getAllParams($this->connexion);

        $this->custoResponse->setData($paramList);
        $this->custoResponse->setCount(count($paramList));
        $jsonResponse = $this->custoResponse->getJsonResponse();

        $response = new JsonResponse(
            $jsonResponse, 
            Response::HTTP_OK, 
            array(), 
            false); // content, status, headers, false if already json

        return $response;
    }

/**
* @Get(
*   path="/params/0/{name}",
*   summary="Returns a contest parameter identified by its name",
*   tags={"Param"},
*   operationId="getParamByName",
*   @OA\Parameter(
*       name="name",
*       in="path",
*       description="param name",
*       required=true,
*       @OA\Schema(
*           type="string"
*       )
*   ),
*   @OA\Response(
*       response="200",
*       description="parameter",
*       @OA\JsonContent(type="array", description="one parameters", @OA\Items(ref="#/components/schemas/Param"))
*   )
* )
*/
    public function getParamByName(Request $request): Response
    {
        $name = $request->attributes->get('name','');
        $paramList = $this->paramRepository->getParamByName($this->connexion, $name);

        $this->custoResponse->setData($paramList);
        $this->custoResponse->setCount(count($paramList));
        $jsonResponse = $this->custoResponse->getJsonResponse();

        $response = new JsonResponse(
            $jsonResponse, 
            Response::HTTP_OK, 
            array(), 
            false); // content, status, headers, false if already json

        return $response;
    }

}
