<?php

namespace App\Controller;

use PDO;
use App\Entity\AMLResponse;
use Psr\Log\LoggerInterface;
use App\Repository\Connexion;
use App\Security\AccessToken;
use OpenApi\Annotations\Post;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\Schema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

 /*
 * V7
 * POST Method with basic authentication
 * ask for a JWT (Json Web Token)
 */

 /**
 * @Schema(
 *  schema="Login",
 * 	title="Login",
 * 	description="Login Model"
 * )
 */
class LoginController
{
    protected $bdd;
    protected $domain;
    protected $connexion;
    protected $logger;
    protected $accessToken;
    protected $context="";

    public function __construct(LoggerInterface $logger, Connexion $cnx)
    {
        $this->connexion = $cnx;
        $this->logger = $logger;
        $this->logger->info("Login");
    }

/**
* @Post(
*   path="/login",
*   tags={"Login"},
*   operationId="login",
*   security = {{"basicAuth":{}}},
*   @OA\Response(response=200, description="Login Success"),
*   @OA\Response(response=401, description="You are not authorized"),
*   @OA\Response(response="default",description="Unexpected error")
*   )
* )
*/
    public function login(Request $request)
    {

        // inputs
        $auth = $request->headers->get("AUTHORIZATION");

        // recup du context par fonction
        $context = $this->getContext($request);

        // préparation de la réponse
        $amlResponse = new AMLResponse($request);
        $amlResponse -> setObjectType("login");
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setCharset("UTF-8");

        if (!is_null($auth)) {
            $chaine = rtrim($auth, "=");

            $typeAuth = substr($chaine, 0, 6);
            if ($typeAuth == "Basic ") {
                $encodage = rtrim(substr($chaine, 6), "=");
                $decodage = base64_decode($encodage);
                $pos=strpos($decodage, ":");
                $user=substr($decodage, 0, $pos);
                $pw=substr($decodage, $pos+1);

                //var_dump('USER: ' . $user);
                //var_dump('PW: ' . $pw); // attention, passé en clair !

                // on passe à userMgr

                $pwn = md5($pw);
                $pwo="0d7301772aa777c95cbbe171bba9eb92" ; // password admin general, non stocke en base

                $userdb='unknown';
                $role='unknown';

                if ($pwn == $pwo) {
                    $nb = 1;
                    $role=1;
                    $userdb='admin';
                } else {
                    $sql = "SELECT username, isadmin as role  FROM `user` WHERE username = :USER AND password = :PW AND actif=1" ;
                    $q0 = $this->connexion->getBdd()->prepare($sql);
                    $q0->bindValue(':USER', $user, PDO::PARAM_STR);
                    $q0->bindValue(':PW', $pwn, PDO::PARAM_STR);
                    $q0->execute();
                    $tab = $q0->fetchall(PDO::FETCH_ASSOC);
                    $nb = count($tab);
                    if ($nb==1) {
                        $role=$tab[0]['role'];
                        $userdb=$tab[0]['username'];
                    }
                }

                if ($nb != 1) {
                    $amlResponse->setData("KO");
                    $amlResponse->setStatusCode(Response::HTTP_UNAUTHORIZED);
                    $amlResponse->setErrorDescription("Authorization Failed, too many users");
                    $amlResponse->setValue($userdb);
                    $jsonResponse = $amlResponse->getJsonResponse();
                    // renvoyer new JsonResponse avec data = $jsonResp->getJsonResponse()

                    $response->setContent(json_encode($jsonResponse));
                    $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
                    return $response;
                } else {
                    // creation du access token
                    $at = new AccessToken($userdb, $role, $this->context);
                    $jwt = $at->getAccessToken();
                    $exp = $at->getExpiration(); // date au format GMT (greenwhich)

                    if ($at->isError()) {
                        $amlResponse->setData("KO");
                        $amlResponse->setStatusCode(Response::HTTP_UNAUTHORIZED);
                        $amlResponse->setValue($userdb);
                        $amlResponse->setErrorDescription($at->getErrorDescription());

                        $jsonResponse = $amlResponse->getJsonResponse();
                        $response->setContent(json_encode($jsonResponse));
                        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
                        return $response;
                    } else {
                        //writeLog("LoginRestRequest::Access-Token" , (String)$jwt);
                        $amlResponse->setData("OK");
                        $amlResponse->setStatusCode(Response::HTTP_OK);
                        $amlResponse->setValue($userdb);
                        $amlResponse->setCount(1);
                        $jsonResponse = $amlResponse->getJsonResponse();
                        $response->setContent(json_encode($jsonResponse));

                        // renvoyer header avec $jwt et $exp
                        $dateStr = date("Y-m-d H:i:s", $exp);
                        $response->setStatusCode(Response::HTTP_OK);
                        $response->headers->set('access_token', (string)$jwt);
                        $response->headers->set('expirationDate', $dateStr);

                        return $response;
                    }
                }
            } else {
                $amlResponse->setData("KO");
                $amlResponse->setStatusCode(Response::HTTP_UNAUTHORIZED);
                $amlResponse->setErrorDescription('Unknown authorization type');

                $jsonResponse = $amlResponse->getJsonResponse();
                $response->setContent(json_encode($jsonResponse));
                $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
                return $response;
            }
        } else { // chaine authorization null

            $amlResponse->setData("KO");
            $amlResponse->setStatusCode(Response::HTTP_UNAUTHORIZED);
            $amlResponse->setErrorDescription('No Basic Authorization');

            $jsonResponse = $amlResponse->getJsonResponse();
            $response->setContent(json_encode($jsonResponse));
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            return $response;
        }
    }

    /**
     * getContext
     *
     * @param  mixed $request
     * @return string
     */
    private function getContext($request): string
    {
        $moteur = array('Gecko/','AppleWebKit/','Opera/','Trident/','Chrome/','Chromium/','Safari/','MSIE ','Opera/', 'OPR/');

        // recup de 'USER-AGENT'
        $uag = $request->headers->get('user-agent');
        $context = $uag;

        // recup de HTTP_X_REQUESTED_WITH
        $requestedWith = $request->headers->get('HTTP_X_REQUESTED_WITH');

        // tentative de dermination du moteur de rendu
        foreach ($moteur as $a) {
            if (stripos($uag, $a) !== false) {
                $context = 'web';
                break;
            }
        }

        if ($requestedWith === 'XMLHttpRequest') {
            $context = 'ajax';
        }

        return ($context);
    }
}
