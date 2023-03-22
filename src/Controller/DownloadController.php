<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Repository\Connexion;
use App\Repository\DownloadRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class DownloadController extends parentController
{

    protected $at;
    protected $downloadRepository;

    public function __construct(
        DownloadRepository $rep,
        LoggerInterface $logger, 
        Connexion $cnx)
    {

        // appel du parent Controller pour les fonctions et properties communes
        parent::__construct();

        $logger->info("DownloadController construct");

        $this->downloadRepository = $rep; // self
        $this->connexion = $cnx; // parent
        $this->downloadRepository->setConnexion($cnx);
        $this->logger = $logger; // parent
        $this->custoResponse->setobjectType("download"); 

        $this->at = $this->getRequest()->headers->get('access-token'); // string|null

    }

    public function downloadGet(Request $request) : Response {

        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction (parent)
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at);

        if (strlen($sIsAuthorized)!==0) {
            $response = $this->returnNotAuthorized($sIsAuthorized);
        } else { // pas d'erreur
            $id = $request->attributes->get('id', 0);
            $ar = $this->downloadRepository->downloadGet($this->connexion, $id);
            //$this->logger->info(json_encode($ar));
            // "size":0
            if((int)$ar['size'] !== 0) {
                $response = new BinaryFileResponse($ar['file']);
            } else {
                $this->logger->error($ar['error']);
                $response = new JsonResponse(
                    $ar['error'], 
                    Response::HTTP_NO_CONTENT, // OK
                ); 
            }
        }
        return $response;
    }


}
