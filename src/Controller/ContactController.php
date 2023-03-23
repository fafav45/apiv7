<?php

namespace App\Controller;

use OpenApi\Annotations\Get;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use App\Repository\Connexion;
use App\Repository\ContactRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends ParentController
{
    protected $contactRepository;


    public function __construct(
        ContactRepository $rep, 
        LoggerInterface $logger, 
        Connexion $cnx)
    {
        // appel du parent Controller pour les fonctions et properties communes
        parent::__construct();

        $logger->info("ContactController construct");
        $this->contactRepository = $rep; // self
        $this->connexion = $cnx; // parent
        $this->contactRepository->setConnexion($cnx);
        $this->logger = $logger; // parent
        //$this->custoResponse // parent
        $this->custoResponse->setobjectType("contacts"); // files pour FileController
    }

/**
* @Get(
*   path="/contacts",
*   summary="Returns a list of contacts",
*   tags={"Contacts"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="getAllContacts",
*
*   @OA\Response(
*       response=200,
*       description="OK",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Contacts"),
*		        @OA\Schema(ref="#/components/schemas/Response"),
* 	        }
*       )
*   ),
*   @OA\Response(
*       response=401,
*       description="UNAUTHORIZED",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Contacts"),
*		        @OA\Schema(ref="#/components/schemas/Response"),
* 	        }
*       )
*   ),
*   @OA\Response(
*       response="default",
*       description="Unexpected error",
*       @OA\JsonContent(ref="#/components/schemas/Response")
*   )
* )
*/
    public function getAllContacts(Request $request): Response {

        // on recupere le token depuis le header
        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction (parent)
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at);

        if (strlen($sIsAuthorized)!==0) {
            return $this->returnNotAuthorized($sIsAuthorized);
        } else {
            $contactList = $this->contactRepository->getAll($this->connexion);

            $response = new Response(
                $contactList, 
                Response::HTTP_OK, 
                array("Content-Type" => "text/csv")
            ); // content, status, headers

            return $response;
        }
    }
}
