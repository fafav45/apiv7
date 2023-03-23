<?php

namespace App\Controller;


use App\Entity\Parameter;
use OpenApi\Annotations\Get;
use OpenApi\Annotations\Put;
use Psr\Log\LoggerInterface;
use App\Repository\Connexion;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\Delete;
use App\Repository\EntryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class EntryController extends ParentController
{

    protected $at;
    protected $entryRepository;
    protected  $sumOnly         = false;
    protected $countOnly        = false;
    protected  $totalCountOnly = false;
    protected  $profId         = null;
    protected  $studentId      = null;
    protected  $objectType          ;
    protected  $nature          = "";
    protected  $natureId       = 0;
    protected $musicValidated   = false;
    protected $java             = false;
    protected $national         = false;
    protected $csv              = false;
    protected $followUp         = false;
    protected $music            = false;
    protected $nomRegion        = "CND";
    protected $startDate        = "2024-01-01";
    protected $maxEntries;

    const ARRAY_NATURE = array('individual','individuel','ind','duet','duo','group','groupe','grp'); 

    public function __construct(
        EntryRepository $rep,
        LoggerInterface $logger, 
        Connexion $cnx)
    {

        // appel du parent Controller pour les fonctions et properties communes
        parent::__construct();

        $logger->info("EntryController construct");

        $this->entryRepository = $rep; // self
        $this->connexion = $cnx; // parent
        $this->entryRepository->setConnexion($cnx);
        $this->logger = $logger; // parent
        $this->custoResponse->setobjectType("entries"); 

        // recup request
        // dans parent via getRequest()
        // Available values : followUp, java, music, musicValidated, sumOnly, national, csv

        $this->at                   = $this->getRequest()->headers->get('access-token'); // string|null
        $this->sumOnly              = (bool)$this->getRequest()->query->get('sumOnly', 0);
        $this->countOnly            = (bool)$this->getRequest()->query->get('countOnly', 0);
        $this->totalCountOnly       = (bool)$this->getRequest()->query->get('totalCountOnly', 0);
        $this->musicValidated       = (bool)$this->getRequest()->query->get('musicValidated', 0);
        $this->java                 = (bool)$this->getRequest()->query->get('java', 0);
        $this->national             = (bool)$this->getRequest()->query->get('national', 0);
        $this->profId               = $this->getRequest()->query->get('teacherId', 0);
        $this->studentId            = $this->getRequest()->query->get('studentId', 0);
        $this->natureId             = $this->getRequest()->query->get('natureId', 0);
        $this->objectType           = $this->getRequest()->query->get('objectType', '');
        $this->nature               = $this->getRequest()->query->get('nature', '');
        // nature non utilisé dans query, seulement dans attributes
        $this->csv                  = (bool)$this->getRequest()->query->get('csv', 0);
        $this->followUp             = (bool)$this->getRequest()->query->get('followUp', 0);
        $this->music                = (bool)$this->getRequest()->query->get('music', 0);

        /*
        * http://{{host}}/api/v7/entries
        * http://{{host}}/api/v7/entries?musicValidated=1
        * followUp=1
        * java=1
        * http://{{host}}/api/v7/entries?java=1&national=1
        * csv=1
        * countOnly
        *
        * teacherId=2
        * studentId=22
        * sumOnly=1
        * totalCountOnly=1
        */


        // recup des paramètres
        // - region
        // - dateCalcul
        // - maxpassages

        $fichierIniAdmin = $this->connexion->getIniAdmin();
        $fichierIni = $this->connexion->getIniRegion();

        if (!file_exists($fichierIni)) {
            $this->custoResponse->setErrorDescription("parameters file not found");
            $this->custoResponse->setErrorType('parameter error');
        }
        if (!file_exists($fichierIniAdmin)) {
            $this->custoResponse->setErrorDescription("admin parameters file not found");
            $this->custoResponse->setErrorType('parameter error');
        }
    
        $oIni=new Parameter();
        $oIniAdmin=new Parameter();

        $oIni->m_fichier($fichierIni);
        $oIniAdmin->m_fichier($fichierIniAdmin);

        $tabParam = $oIni->array_groupe() ;
        $tabParamAdmin = $oIniAdmin->array_groupe() ;
        unset($oIni); unset($oIniAdmin);
        $this->nomRegion = $tabParam['nomregion'];
        $this->maxEntries = (int)$tabParam['limitepassage'];
        $maxEntries1 = (int)$tabParam['limitepassage1'];
        $maxEntries2 = (int)$tabParam['limitepassage2'];
        $numberOfContests = (int)$tabParam['nombreconcours'];
        
        unset($tabParam);

        $startDateFr = $tabParamAdmin['currentyear'];
        $date=date_create_from_format("d/m/Y",$startDateFr);
        $this->startDate=date_format($date,"Y-m-d");
        unset($tabParamAdmin);

        if($numberOfContests == 2)
            $this->maxEntries = $maxEntries1 + $maxEntries2;

    }

/**
* @OA\Get(
*   path="/entries/{id}/{nature}",
*   tags={"Entries"},
*   summary="entry identified by its Id and nature",
*     security={
*         {"bearer": {}}
*     },
*   operationId="entryGetById",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="id of entry",
*       required=true,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Parameter(
*       name="nature",
*       in="path",
*       description="nature of entry",
*       required=true,
*       @OA\Schema(
*           type="string",
*           enum={"individual", "duet", "group"}
*       )
*   ),
*   @OA\Response(
*       response=200,
*       description="success",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Entries"),
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
*       response=400,
*       description="Bad Request",
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
    public function entryGetById(Request $request) : ?Response {

        // on recupere le token depuis le header
        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction (parent)
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at);

        if (strlen($sIsAuthorized)!==0) {
            return $this->returnNotAuthorized($sIsAuthorized);
        } else { // pas d'erreur

            // tester valeur de nature
            //$arrayAccept = array('individual','individuel','ind','duet','duo','group','groupe','grp'); 
            $id = $request->attributes->get('id', 0);
            $nature = $request->attributes->get('nature', 'individual');

            if (!in_array(strtolower($nature), SELF::ARRAY_NATURE)) {
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
            }

            $list = $this->entryRepository->entryGetById($id, $nature);
            $this->logger->debug("id= $id");

            $this->custoResponse->setData($list);
            $this->custoResponse->setCount(count($list));
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
* @OA\Get(
*   path="/individuals/{id}",
*   tags={"Entries"},
*   summary="individual entry identified by its Id",
*     security={
*         {"bearer": {}}
*     },
*   operationId="individualGetById",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="id of entry",
*       required=true,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Response(
*       response=200,
*       description="success",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Entries"),
*		        @OA\Schema(ref="#/components/schemas/Response")
* 	        }
*       )
*   ),
*   @OA\Response(
*       response=400,
*       description="Bad Request",
*       @OA\JsonContent(
* 	        allOf={
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
    public function individualGetById(Request $request) : ?Response {
        /* attributes :
        * id
        * set nature attribute to "individual"
        * redirect to route entryGetById
        */
        $request->attributes->set("nature", "individual");
        $this->logger->info("redirecting from individualGetById to entryGetById");
        $response = $this->entryGetById($request);
        return $response;
    }

/**
* @OA\Get(
*   path="/duets/{id}",
*   tags={"Entries"},
*   summary="duet entry identified by its Id",
*     security={
*         {"bearer": {}}
*     },
*   operationId="duetGetById",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="id of entry",
*       required=true,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Response(
*       response=200,
*       description="success",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Entries"),
*		        @OA\Schema(ref="#/components/schemas/Response")
* 	        }
*       )
*   ),
*   @OA\Response(
*       response=400,
*       description="Bad Request",
*       @OA\JsonContent(
* 	        allOf={
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
public function duetGetById(Request $request) : ?Response {
        /* attributes :
        * id
        * set nature attribute to "duet"
        * redirect to route entryGetById
        */
        $request->attributes->set("nature", "duet");
        $this->logger->info("redirecting from duetGetById to entryGetById");
        $response = $this->entryGetById($request);
        return $response;
}
/**
* @OA\Get(
*   path="/groups/{id}",
*   tags={"Entries"},
*   summary="group entry identified by its Id",
*     security={
*         {"bearer": {}}
*     },
*   operationId="groupGetById",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="id of entry",
*       required=true,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Response(
*       response=200,
*       description="success",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Entries"),
*		        @OA\Schema(ref="#/components/schemas/Response")
* 	        }
*       )
*   ),
*   @OA\Response(
*       response=400,
*       description="Bad Request",
*       @OA\JsonContent(
* 	        allOf={
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
public function groupGetById(Request $request) : ?Response {
        /* attributes :
        * id
        * set nature attribute to "group"
        * redirect to route entryGetById
        */
        $request->attributes->set("nature", "group");
        $this->logger->info("redirecting from groupGetById to entryGetById");
        $response = $this->entryGetById($request);
        return $response;
}

/**
* @OA\Get(
*   path="/entries",
*   tags={"Entries"},
*   summary="Returns a list of entries",
*   description="Optional: ?teacherid={id}, ?studentid={id}, ?opt=musicValidated, ?opt=music, ?opt=sumOnly, ?opt=java, ?opt=followUp, ?opt=csv",
*     security={
*         {"bearer": {}}
*     },
*   operationId="entryGet",
*   @OA\Parameter(
*       name="teacherid",
*       in="query",
*       description="id teacher",
*       required=false,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Parameter(
*       name="studentid",
*       in="query",
*       description="id student",
*       required=false,
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
*           enum={"followUp", "java", "music", "musicValidated", "sumOnly", "national", "csv"}
*       )
*   ),
*   @OA\Response(
*       response="200",
*       description="success",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Entries"),
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
    public function entryGet(Request $request) : ?Response {

                // on recupere le token depuis le header
        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction (parent)
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at);

        if (strlen($sIsAuthorized)!==0) {
            return $this->returnNotAuthorized($sIsAuthorized);
        } else { // pas d'erreur

            if (empty($this->nature)) {
                $this->nature = $request->attributes->get('nature', '');
            }

            if ($this->music && !empty($this->nature) ) {
                $list = $this->entryRepository->getMusicListPerNature($this->nature);
                $this->custoResponse->setCount(count($list));

            } else if ($this->followUp) {
                $list = $this->entryRepository->apiFollowUp();
                $this->custoResponse->setCount(count($list));

            } else if ($this->java) {
                $list = $this->entryRepository->apiGetListForJava($this->nature, $this->national);
                $this->custoResponse->setCount(count($list));

            } else if ($this->csv) {
                $rawData = $this->entryRepository->apiCsv($this->connexion->getLanguage(), $this->startDate, $this->nomRegion);            
                return new Response(
                    $rawData,
                    Response::HTTP_OK,
                    ["Content-Type"=>"text/csv;charset=UTF-8", "Pragma"=>"no-cache","Cache-Control"=>"no-store, no-cache, must-revalidate, max-age=0"]
                );
            } else if ($this->totalCountOnly) {
                $list = $this->entryRepository->entryGetAll($this->musicValidated, $this->countOnly, $this->sumOnly, $this->nature, $this->profId, $this->studentId, $this->maxEntries);
                $this->custoResponse->setCount(count($list));
                $list=[];
            } else {
                $list = $this->entryRepository->entryGetAll($this->musicValidated, $this->countOnly, $this->sumOnly, $this->nature, $this->profId, $this->studentId, $this->maxEntries);
                $this->custoResponse->setCount(count($list));
            }

        
            $this->custoResponse->setData($list);
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
*   path="/duets",
*   tags={"Entries"},
*   summary="Returns a list of duets",
*   description="Optional: ?teacherid={id}, ?studentid={id}, ?musicValidated=1, music=1, sumOnly=1, java=1",
*     security={
*         {"bearer": {}}
*     },
*   operationId="duetGet",
*   @OA\Parameter(
*       name="teacherid",
*       in="query",
*       description="id teacher",
*       required=false,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Parameter(
*       name="studentid",
*       in="query",
*       description="id student",
*       required=false,
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
*           enum={"java", "music", "musicValidated", "sumOnly", "national"}
*       )
*   ),
*   @OA\Response(
*       response="200",
*       description="success",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Entries"),
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
    public function duetGet(Request $request) : ?Response {
        /* attributes :
        * set nature attribute to "duet"
        * redirect to route entryGetById
        */
        $request->attributes->set("nature", "duet");
        $this->logger->info("redirecting from duetGet to entryGet");
        $response = $this->entryGet($request);
        return $response;
}

/**
* @OA\Get(
*   path="/groups",
*   tags={"Entries"},
*   summary="Returns a list of groups",
*   description="Optional: ?teacherid={id}, ?studentid={id}, ?musicValidated=1, music=1, sumOnly=1, java=1",
*     security={
*         {"bearer": {}}
*     },
*   operationId="groupGet",
*   @OA\Parameter(
*       name="teacherid",
*       in="query",
*       description="id teacher",
*       required=false,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Parameter(
*       name="studentid",
*       in="query",
*       description="id student",
*       required=false,
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
*           enum={"java", "music", "musicValidated", "sumOnly", "national"}
*       )
*   ),
*   @OA\Response(
*       response="200",
*       description="success",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Entries"),
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
public function groupGet(Request $request) : ?Response {
        /* attributes :
        * set nature attribute to "individual"
        * redirect to route entryGetById
        */
        $request->attributes->set("nature", "group");
        $this->logger->info("redirecting from groupGet to entryGet");
        $response = $this->entryGet($request);
        return $response;
}

/**
* @OA\Get(
*   path="/individuals",
*   tags={"Entries"},
*   summary="Returns a list of individuels",
*   description="Optional: ?teacherid={id}, ?studentid={id}, ?musicValidated=1, music=1, sumOnly=1, java=1",
*     security={
*         {"bearer": {}}
*     },
*   operationId="individualGet",
*   @OA\Parameter(
*       name="teacherid",
*       in="query",
*       description="id teacher",
*       required=false,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Parameter(
*       name="studentid",
*       in="query",
*       description="id student",
*       required=false,
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
*           enum={"java", "music", "musicValidated", "sumOnly", "national"}
*       )
*   ),
*   @OA\Response(
*       response="200",
*       description="success",
*       @OA\JsonContent(
* 	        allOf={
*		        @OA\Schema(ref="#/components/schemas/Entries"),
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
public function individualGet(Request $request) : ?Response {
        /* attributes :
        * set nature attribute to "individual"
        * redirect to route entryGetById
        */
        $request->attributes->set("nature", "individual");
        $this->logger->info("redirecting from individualGet to entryGet");
        $response = $this->entryGet($request);
        return $response;
}
    
/**
* @OA\Put(
*   path="/entries/{id}/{nature}/{type}/{value}",
*   summary="update an entry",
*   tags={"Entries"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="entryPut",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="entry id",
*       required=true,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Parameter(
*       name="nature",
*       in="path",
*       description="nature of entry",
*       required=true,
*       @OA\Schema(
*           type="string",
*           enum={"individual", "duet", "group"}
*       )
*   ),
*   @OA\Parameter(
*       name="type",
*       in="path",
*       description="operation type",
*       required=true,
*       @OA\Schema(
*           type="string",
*           enum={"valmp3","comment"}
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
*       response=401,
*       description="UNAUTHORIZED",
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
    public function entryPut(Request $request) : ?Response {

        // path="/entries/{id}/{nature}/{type}/{value}"

        // on recupere le token depuis le header
        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction (parent)
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at);

        if (strlen($sIsAuthorized)!==0) {
            return $this->returnNotAuthorized($sIsAuthorized);
        } else {


            $arrayAcceptType = array('VALMP3','COMMENT' ); 
            // SELF::ARRAY_NATURE

            $id = (int)$request->attributes->get('id', 0);
            $nature = $request->attributes->get('nature', 'individual');
            $type = $request->attributes->get('type', '');
            $value = $request->attributes->get('value', '');
            

            $this->custoResponse->setId($id);
            $this->custoResponse->setValue($value);
            $this->custoResponse->setType($type);

            if (!in_array(strtoupper($type), $arrayAcceptType)) {
                $this->custoResponse->setErrorDescription("Type $type Not Allowed");
                $this->custoResponse->setErrorType("Operation");
                $this->custoResponse->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
    
                $jsonResponse = $this->custoResponse->getJsonResponse();
    
                $response = new JsonResponse(
                    $jsonResponse, 
                    Response::HTTP_METHOD_NOT_ALLOWED, 
                    array(), 
                    false); // content, status, headers, false if already json
                return $response;
            }

            if (!in_array(strtolower($nature), SELF::ARRAY_NATURE)) {
                $this->custoResponse->setErrorDescription("Nature $nature Not Allowed");
                $this->custoResponse->setErrorType("Operation");
                $this->custoResponse->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
    
                $jsonResponse = $this->custoResponse->getJsonResponse();
    
                $response = new JsonResponse(
                    $jsonResponse, 
                    Response::HTTP_METHOD_NOT_ALLOWED, 
                    array(), 
                    false); // content, status, headers, false if already json
                return $response;
            } 


            $iStatus = -1;

            switch (mb_strtoupper($type)) {
                case 'COMMENT':
                    $iStatus = $this->entryRepository->updateMP3Comment($id, $nature, $value);
                break;
                case 'VALMP3':
                    $iStatus = $this->entryRepository->updateMP3Validation($id, $nature, $value);
                break;
            }


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

/**
* @OA\Put(
*   path="/individuals/{id}/{type}/{value}",
*   summary="update an individual entry",
*   tags={"Entries"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="individualPut",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="entry id",
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
*           enum={"valmp3","comment"}
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
*       response=401,
*       description="UNAUTHORIZED",
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
    public function individualPut(Request $request) : ?Response {
        // path="/individuals/{id}/{type}/{value}"

        /* attributes :
        * id
        * set nature attribute to "individual"
        * redirect to route entryPut
        */

        $request->attributes->set("nature", "individual");
        $this->logger->info("redirecting from individualPut to entryPut");
        $response = $this->entryPut($request);
        return $response;
    }

/**
* @OA\Put(
*   path="/duets/{id}/{type}/{value}",
*   summary="update an duet entry",
*   tags={"Entries"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="duetPut",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="entry id",
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
*           enum={"valmp3","comment"}
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
*       response=401,
*       description="UNAUTHORIZED",
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
public function duetPut(Request $request) : ?Response {
        // path="/duets/{id}/{type}/{value}"

        /* attributes :
        * id
        * set nature attribute to "duet"
        * redirect to route entryPut
        */
        $request->attributes->set("nature", "duet");
        $this->logger->info("redirecting from duetPut to entryPut");
        $response = $this->entryPut($request);
        return $response;
}

/**
* @OA\Put(
*   path="/groups/{id}/{type}/{value}",
*   summary="update an group entry",
*   tags={"Entries"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="groupPut",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="entry id",
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
*           enum={"valmp3","comment"}
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
*       response=401,
*       description="UNAUTHORIZED",
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
public function groupPut(Request $request) : ?Response {
        // path="/groups/{id}/{type}/{value}"

        /* attributes :
        * id
        * set nature attribute to "group"
        * redirect to route entryPut
        */
        $request->attributes->set("nature", "duet");
        $this->logger->info("redirecting from groupPut to entryPut");
        $response = $this->entryPut($request);
        return $response;
}
/**
* @OA\Delete(
*   path="/entries/{id}/{nature}",
*   summary="delete an entry",
*   tags={"Entries"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="entryDelete",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="entry id",
*       required=true,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Parameter(
*       name="nature",
*       in="path",
*       description="entry nature",
*       required=true,
*       @OA\Schema(
*           type="string",
*           enum={"individual", "duet", "group"}
*       )
*   ),
*   @OA\Response(
*       response=200,
*       description="delete entry reponse",
*       @OA\JsonContent(
* 	        allOf={
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
    public function entryDelete(Request $request) : ?Response {
        // on recupere le token depuis le header
        $at = $request->headers->get('access-token'); // string|null

        // recup du context par fonction (parent)
        $context = $this->getApiContext($request); // ex: PostmanRuntime/7.26.8

        $sIsAuthorized = $this->isApiAuthorized($context, $at);

        if (strlen($sIsAuthorized)!==0) {
            return $this->returnNotAuthorized($sIsAuthorized);
        } else {
            $id = (int)$request->attributes->get('id', 0);
            $nature = $request->attributes->get('nature', 0);

            $this->logger->info("entry Delete id: $id");
            $this->logger->info("entry Delete nature: $nature");
            $this->custoResponse->setId($id);

            $entryStatus = $this->entryRepository->entryDelete($id, $nature);
            $this->logger->info("entryStatus: $entryStatus");

            if ($entryStatus !==  1) {
                $this->logger->info("entryStatus: failed");
                $this->custoResponse->setErrorDescription("unknown");
                $this->custoResponse->setErrorType('database error');
                $this->custoResponse->setStatusCode(304); 
            } else {
                $this->logger->info("entryStatus: success");
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

/**
* @OA\Delete(
*   path="/individuals/{id}",
*   summary="delete an entry",
*   tags={"Entries"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="entryDelete",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="entry id",
*       required=true,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Response(
*       response=200,
*       description="delete entry reponse",
*       @OA\JsonContent(
* 	        allOf={
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
    public function individualDelete(Request $request) : ?Response {
        /* attributes :
        * id
        * set nature attribute to "individual"
        * redirect to route entryGetById
        */
        $request->attributes->set("nature", "individual");
        $this->logger->info("redirecting from individualDelete to entryDelete");
        $response = $this->entryDelete($request);
        return $response;
    }

/**
* @OA\Delete(
*   path="/duets/{id}",
*   summary="delete an entry",
*   tags={"Entries"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="duetDelete",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="entry id",
*       required=true,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Response(
*       response=200,
*       description="delete entry reponse",
*       @OA\JsonContent(
* 	        allOf={
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
    public function duetDelete(Request $request) : ?Response {
        /* attributes :
        * id
        * set nature attribute to "duet"
        * redirect to route entryGetById
        */
        $request->attributes->set("nature", "duet");
        $this->logger->info("redirecting from duetDelete to entryDelete");
        $response = $this->entryDelete($request);
        return $response;
    }

/**
* @OA\Delete(
*   path="/groups/{id}",
*   summary="delete an entry",
*   tags={"Entries"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="groupDelete",
*   @OA\Parameter(
*       name="id",
*       in="path",
*       description="entry id",
*       required=true,
*       @OA\Schema(
*           type="integer"
*       )
*   ),
*   @OA\Response(
*       response=200,
*       description="delete entry reponse",
*       @OA\JsonContent(
* 	        allOf={
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
    public function groupDelete(Request $request) : ?Response {
            /* attributes :
            * id
            * set nature attribute to "group"
            * redirect to route entryGetById
            */
            $request->attributes->set("nature", "group");
            $this->logger->info("redirecting from groupDelete to entryDelete");
            $response = $this->entryDelete($request);
            return $response;
    }
}
