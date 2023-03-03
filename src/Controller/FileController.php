<?php

namespace App\Controller;

//use OA\Get;
//use OA\Delete;
use OpenApi\Annotations\Get;
use OpenApi\Annotations\Delete;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use App\Repository\Connexion;
use App\Repository\FileRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

//class FileController extends AbstractController
class FileController extends ParentController
{
    protected $fileRepository;

    /*
    * injection de dependance sur controller (constructeur ou fonction du controller) : FileRepository, LoggerInterface, Connexion
    * controller de service (toutes les classes qui sont dans src)
    * https://www.youtube.com/watch?v=4t3fNkGwRWo&ab_channel=LiorCHAMLA-WebDevelopMe
    * 1:56:17
    * 1:13:36
    * php bin/console debug:autowiring
    * php bin/console debug:autowiring Repository --all
    * Psr\Log\LoggerInterface, App\Controller\FileController, App\Repository\Connexion, App\Repository\FileRepository
    * extends ParentController
    *
    * logs ecrits dans la valeur definie dans _conf.php (ini_set("error_log", 'ins-2023.log');)
    */
    public function __construct(
        FileRepository $rep, 
        LoggerInterface $logger, 
        Connexion $cnx)
    {

        // appel du parent Controller pour les fonctions et properties communes
        parent::__construct();

        $logger->info("FileController construct");

        $logger->info('Hey ! I am writing in logs !!');
        $logger->critical('Oops something bad is happening');

        $this->fileRepository = $rep; // self
        $this->connexion = $cnx; // parent
        $this->fileRepository->setConnexion($cnx);
        $this->logger = $logger; // parent
        //$this->custoResponse // parent
        $this->custoResponse->setobjectType("files"); // files pour FileController

        //$this->logger->

        //dump($this->fileRepository);
        //$tutu = $this->connexion->getBdd();

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


    /**
    * @Get(
    *   path="/files",
    *   summary="Returns a list of availables files (certificates, IDs, photoIds, musics)",
    *   tags={"Files"},
    *     security={
    *         {"bearer": {}}
    *     },
    *   operationId="getAllFiles",
    *
    *   @OA\Parameter(
    *       name="opt",
    *       in="query",
    *       description="option",
    *       required=false,
    *       @OA\Schema(
    *           type="string",
    *           enum={"musicsCountOnly", "certificatesCountOnly", "IDsCountOnly", "IDPhotosCountOnly", "mp3Only"}
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="OK",
    *       @OA\JsonContent(
    * 	        allOf={
    *		        @OA\Schema(type="object", ref="#/components/schemas/Files"),
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
    public function getAllFiles(Request $request): Response
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
            $musicsCountOnly = (bool)$request->query->get('musicsCountOnly', 0);
            $certificatesCountOnly = (bool)$request->query->get('certificatesCountOnly', 0);
            $IDsCountOnly = (bool)$request->query->get('IDsCountOnly', 0);
            $IDPhotosCountOnly = (bool)$request->query->get('IDPhotosCountOnly', 0);
            $mp3Only = (bool)$request->query->get('mp3Only', 0);

            $fileList = $this->fileRepository->findAllFiles($this->connexion, $musicsCountOnly, $certificatesCountOnly, $IDsCountOnly, $IDPhotosCountOnly, $mp3Only);

            // custoResponse cree dans Parent
            $this->custoResponse->setData($fileList);
            $this->custoResponse->setCount(count($fileList));
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
    *   path="/files/{id}",
    *   summary="Returns a file identified by its id",
    *   tags={"Files"},
    *     security={
    *         {"bearer": {}}
    *     },
    *   operationId="getOneFile",
    *
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="file id",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *
    *   @OA\Parameter(
    *       name="opt",
    *       in="query",
    *       description="option",
    *       required=false,
    *       @OA\Schema(
    *           type="string",
    *           enum={"MD5"}
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="OK",
    *       @OA\JsonContent(
    * 	        allOf={
    *		        @OA\Schema(type="object", ref="#/components/schemas/Files"),
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
    public function getOneFile(Request $request, int $id): Response
    {

        // on recupere le token depuis le header
        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at); // null|string

        if (strlen($sIsAuthorized)!==0) { // error
            return $this->returnNotAuthorized($sIsAuthorized);
        } else {
            $withMD5 = (bool)$request->query->get('withMD5', 0);
            $id = $request->attributes->get('id', 0);
            $file = $this->fileRepository->findOneFile($this->connexion, $id, $withMD5);

            if (count($file)>0) {

                // custoResponse cree dans Parent
                $this->custoResponse->setData($file);
                $this->custoResponse->setId($id);
                $this->custoResponse->setCount(1);
                $this->custoResponse->setStatusCode(Response::HTTP_OK);
                $jsonResponse = $this->custoResponse->getJsonResponse();

                $response = new JsonResponse(
                    $jsonResponse, 
                    Response::HTTP_OK, 
                    array(), 
                    false); // content, status, headers, false if already json

                return $response;
            } else {

                // custoResponse cree dans Parent
                $this->custoResponse->setId($id);
                $this->custoResponse->setCount(0);
                $this->custoResponse->setStatusCode(Response::HTTP_NOT_FOUND);
                //$this->custoResponse->setobjectType("files");
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

    /**
    * @Delete(
    *   path="/files/{id}/{type}",
    *   summary="deletes a file identified by its id and type",
    *   tags={"Files"},
    *     security={
    *         {"bearer": {}}
    *     },
    *   operationId="delOneFileByIdAndType",
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="student_id or teacher_id",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="type",
    *       in="path",
    *       description="file type",
    *       required=true,
    *       @OA\Schema(
    *           type="string",
    *           enum={"studentPhotoID","teacherPhotoID"}
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
    public function delOneFileByIdAndType(Request $request): JsonResponse
    {
        // on recupere le token depuis le header
        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at); // string

        $id = $request->attributes->get('id', 0);
        $fileType = $request->attributes->get('type', "");

        if (strlen($sIsAuthorized)!==0) { // error
            return $this->returnNotAuthorized($sIsAuthorized);
        } else {
            $id = $request->attributes->get('id', 0);

            $count = $this->fileRepository->delOneFileByIdAndType($this->connexion, $id, $fileType);

            // custoResponse cree dans Parent
            $this->custoResponse->setId($id);
            $this->custoResponse->setValue($count);

            if ($count < 0) {
                $this->custoResponse->setCount(0);
            }

            if ($count == -1) { // not found

                $this->custoResponse->setStatusCode(Response::HTTP_NOT_FOUND);
                $this->custoResponse->setErrorDescription($this->connexion->getBdd()->errorInfo());
                $this->custoResponse->setErrorType('No object found');

                $jsonResponse = $this->custoResponse->getJsonResponse();
                $response = new JsonResponse(
                    $jsonResponse, 
                    Response::HTTP_NOT_FOUND, 
                    array(), 
                    false); // content, status, headers, true if already json
                return $response;

            } elseif ($count == -2) { // type not allowed
                $this->custoResponse->setErrorType('Object type');
                $this->custoResponse->setStatusCode(Response::HTTP_BAD_REQUEST);
                $this->custoResponse->setErrorDescription("Option not allowed: $fileType");

                $jsonResponse = $this->custoResponse->getJsonResponse();
                $response = new JsonResponse(
                    $jsonResponse, 
                    Response::HTTP_BAD_REQUEST, 
                    array(), 
                    false); // content, status, headers, true if already json
                return $response;

            } else { // ok
                // custoResponse cree dans Parent
                $this->custoResponse->setId($id);
                $this->custoResponse->setCount($count);
                $this->custoResponse->setStatusCode(Response::HTTP_OK);
                $jsonResponse = $this->custoResponse->getJsonResponse();

                $response = new JsonResponse(
                    $jsonResponse, 
                    Response::HTTP_OK, 
                    array(), 
                    false); // content, status, headers, true if already json
                return $response;
            }
        }
    }

    /**
    * @OA\Delete(
    *   path="/files/{id}",
    *   summary="deletes a file identified by its id",
    *   tags={"Files"},
    *     security={
    *         {"bearer": {}}
    *     },
    *   operationId="delOneFileById",
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="",
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
    public function delOneFileById(Request $request): JsonResponse
    {
        // on recupere le token depuis le header
        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at); // string

        if (strlen($sIsAuthorized)!==0) { // error
            return $this->returnNotAuthorized($sIsAuthorized);
        } else {
            $id = $request->attributes->get('id', 0);

            $count = $this->fileRepository->delOneFileById($this->connexion, $id);

            // custoResponse cree dans Parent
            $this->custoResponse->setId($id);
            $this->custoResponse->setValue($count);

            if ($count == -1) { // ko

                $this->custoResponse->setCount(0);
                $this->custoResponse->setStatusCode(Response::HTTP_NOT_FOUND);
                $this->custoResponse->setErrorDescription($this->connexion->getBdd()->errorInfo());
                $this->custoResponse->setErrorType('No object found');

                $jsonResponse = $this->custoResponse->getJsonResponse();
                $response = new JsonResponse(
                    $jsonResponse, 
                    Response::HTTP_NOT_FOUND, 
                    array(), 
                    false); // content, status, headers, true if already json
                return $response;

            } else { // ok
                // custoResponse cree dans Parent
                $this->custoResponse->setId($id);
                $this->custoResponse->setCount($count);
                $this->custoResponse->setStatusCode(Response::HTTP_OK);
                $jsonResponse = $this->custoResponse->getJsonResponse();

                $response = new JsonResponse(
                    $jsonResponse, 
                    Response::HTTP_OK, 
                    array(), 
                    false); // content, status, headers, true if already json
                return $response;
            }
        }
    }
}
