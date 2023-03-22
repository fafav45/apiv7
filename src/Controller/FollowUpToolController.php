<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Repository\Connexion;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\Schema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

 /*
 * V7
 * POST Method with basic authentication
 * ask for a JWT (Json Web Token)
 */

 /**
 * @Schema(
 *  schema="FollowUpTool",
 * 	title="FollowUpTool",
 * 	description="FollowUpTool Model"
 * )
 */
class FollowUpToolController extends ParentController
{
    protected $connexion;
    protected $logger;

    /**
* @OA\Property(type="integer", description="Major Version")
 */
protected $majorVersion = 0;

/**
* @OA\Property(type="integer", description="Minor Version")
 */
protected $minorVersion = 0;

/**
* @OA\Property(type="string", description="Version")
 */
protected $version = '';

    public function __construct(LoggerInterface $logger, Connexion $cnx)
    {
        parent::__construct();
        $this->connexion = $cnx;
        $this->logger = $logger;
        $logger->info("FollowUpToolController construct");
        $this->custoResponse->setobjectType("tools");
    }

/**
* @OA\Get(
*   path="/followUpToolVersion",
*   summary="Returns the last published version of followup tool",
*   tags={"FollowUpTool"},
*     security={
*         {"bearer": {}}
*     },
*   operationId="followUpTool",
*   @OA\Response(
*       response="200",
*       description="Version",
*       @OA\JsonContent(type="array", description="version", @OA\Items(ref="#/components/schemas/FollowUpTool"))
*   )
* )
*/
    public function followUpTool(Request $request) : Response
    {
        // lecture du fichier de version dans templates/toolbox
        // followUpToolVersion.txt
        // FILEVERSION = 'followUpToolVersion.txt';
        $file = join(DIRECTORY_SEPARATOR, array($this->connexion->getMyrootDir(), 'templates','toolbox', 'followUpToolVersion.txt'));
        if (file_exists($file)) {
            $ressource = fopen($file, 'r');
            $tmp = rtrim(fgets($ressource));
            // format majorVersion.minorVersion
            $tab = explode('.', $tmp);
            if (count($tab) == 2) {
                $this->majorVersion = (int)$tab[0]; 
                $this->minorVersion = (int)$tab[1]; 
                $this->version = $tmp;
                $this->custoResponse->setCount(1);
            } else {
                $this->custoResponse->setErrorDescription('wrong format');
                        
            }
        } else {
            $this->custoResponse->setErrorDescription('file not found');           
        }
        $rawData = array('version' => $this->version, 'name' => 'followUp', 'minorVersion' => $this->minorVersion, 'majorVersion' => $this->majorVersion);
        $this->custoResponse->setData($rawData);
        $jsonResponse = $this->custoResponse->getJsonResponse();

        $response = new JsonResponse(
            $jsonResponse, 
            Response::HTTP_OK, 
            array(), 
            false); // content, status, headers, false if already json
        return $response;
    }
}
