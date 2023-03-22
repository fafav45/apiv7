<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Repository\Connexion;
use App\Repository\DashboardRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Annotations as OA;


class DashboardController extends ParentController
{
    protected $connexion;
    protected $logger;
    protected $dashboardRepository;


    public function __construct(LoggerInterface $logger, Connexion $cnx, DashboardRepository $rep)
    {
        parent::__construct();
        $this->connexion = $cnx; // parent
        $this->logger = $logger; // parent
        $this->dashboardRepository = $rep; // self
        $logger->info("DashboardController construct");
        $this->custoResponse->setobjectType("dashboard"); // parent
    }


/**
* @OA\Get(
*   path="/dashboard",
*   summary="get contest main indicators",
*   tags={"Dashboard"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="dashboard",
*   @OA\Response(
*       response=200,
*       description="OK",
*       @OA\JsonContent(
*           allOf={
*           @OA\Schema(ref="#/components/schemas/Dashboard"),
*           @OA\Schema(ref="#/components/schemas/Response")
*           }
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
    public function dashboard() : Response
    {
        $list = $this->dashboardRepository->dashboard($this->connexion);

        $this->custoResponse->setData($list);
        $this->custoResponse->setCount(1);
        $jsonResponse = $this->custoResponse->getJsonResponse();

        $response = new JsonResponse(
            $jsonResponse, 
            Response::HTTP_OK, 
            array(), 
            false); // content, status, headers, false if already json
        return $response;
    }

}
