<?php

namespace App\Controller;

use App\Security\AccessToken;
use App\Entity\AMLResponse;
use Psr\Log\LoggerInterface;
use App\Repository\Connexion;
use App\Repository\FileRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

define("SECRET", "ed8e871108709b93b0b200ddf19b11be14c417e75efed9d21078efe6efef4880");

//class FileController extends AbstractController
class FileController
{
    protected $fileRepository;
    protected $bdd;
    protected $domain;
    protected $connexion;
    protected $logger;
    protected $accessToken;


    /*
    * injection de dependance sur controller (constructeur ou fonction du controller) : FileRepository, LoggerInterface, Connexion
    * controller de service (toutes les classes qui sont dans src)
    * https://www.youtube.com/watch?v=4t3fNkGwRWo&ab_channel=LiorCHAMLA-WebDevelopMe
    * 1:56:17
    * php bin/console debug:autowiring
    * php bin/console debug:autowiring Repository --all
    * Psr\Log\LoggerInterface, App\Controller\FileController, App\Repository\Connexion, App\Repository\FileRepository
    */
    public function __construct(FileRepository $rep, LoggerInterface $logger, Connexion $cnx)
    {
        //$this->logger->info("FileController construct");
        //dump($rep);

        $this->fileRepository = $rep;
        $this->connexion = $cnx;
        $this->fileRepository->setConnexion($cnx);
        $this->logger = $logger;

        //dump($this->fileRepository);

        // on a $cnx (Objet Connexion)
        //dd($cnx);
    }

    public function index(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/FileController.php',
        ]);
    }


    public function getAll(Request $request): JsonResponse
    {
        $fileList = $this->fileRepository->findAllFiles($this->connexion);

        return new JsonResponse([
            'files' => $fileList,
            Response::HTTP_OK,
            [],
            true
        ]);
    }


    // public function getOne(Request $request, int $id): JsonResponse
    // {
    //     $withMD5 = (bool)$request->query->get('withMD5', 0);
    //     $id = $request->attributes->get('id', 0);
    //     $file = $this->fileRepository->findOne($id, $withMD5);

    //     if($file) {
    //         return new JsonResponse([
    //             'files' => $file,
    //             Response::HTTP_OK,
    //             [],
    //             true
    //         ]);
    //     } 
    //     return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    // }


    //public function getOne(Request $request, int $id): JsonResponse
    public function getOne(Request $request, int $id): Response
    {
        $withMD5 = (bool)$request->query->get('withMD5', 0);
        $id = $request->attributes->get('id', 0);

        $file = $this->fileRepository->findOneFile($this->connexion, $id, $withMD5);

        $amlResponse = new AMLResponse($request);
        $amlResponse->setData($file);
        $amlResponse->setId($id);
        $amlResponse->setCount(1);
        $amlResponse->setobjectType("files");
        $jsonResponse = $amlResponse->getJsonResponse();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($jsonResponse));
        $response->setStatusCode(Response::HTTP_OK);

        if($file) {
            return $response;
            // return new JsonResponse([
            //     $data,              // data
            //     "status" => Response::HTTP_OK // status
            //     /*
            //     [],                // headers
            //     true               // $json
            //     */
            // ]);
        } 

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    protected function getAccessToken() : ?AccessToken {
        return $this ->accessToken;
    }
}
