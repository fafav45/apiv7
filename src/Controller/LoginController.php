<?php

namespace App\Controller;

use PDO;
use App\Entity\AMLResponse;
use Psr\Log\LoggerInterface;
use App\Repository\Connexion;
use App\Security\AccessToken;
use App\Repository\LoginRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

 /*
 * V7
 * POST Method with basic authentication
 * ask for a JWT (Json Web Token)
 */ 

class LoginController
{
    protected $loginRepository;
    protected $bdd;
    protected $domain;
    protected $connexion;
    protected $logger;
    protected $accessToken;

    public function __construct(LoginRepository $rep, LoggerInterface $logger, Connexion $cnx) {
        $this->loginRepository = $rep;
        $this->connexion = $cnx;
        $this->loginRepository->setConnexion($cnx);
        $this->logger = $logger;
    }

    public function login(Request $request) {
        $auth = $request->headers->get("AUTHORIZATION");

        $jsonResp = new AMLResponse($request);
        $jsonResp -> setObjectType("login");
/*
        {
            "objectType": "login",
            "statusCode": 200,
            "statusMessage": null,
            "error": {
                "type": null,
                "description": null
            },
            "login": "OK",
            "common": {
                "method": "POST",
                "id": null,
                "value": null,
                "count": 1,
                "type": ""
            }
        }
*/
        if (!is_null($auth)) {
            $chaine = rtrim($auth,"=");

            $typeAuth = substr($chaine, 0,6);
            if ($typeAuth == "Basic ") {
                $encodage = rtrim(substr($chaine,6),"=");
                $decodage = base64_decode($encodage);
                $pos=strpos($decodage,":");
                $user=substr($decodage,0,$pos);
                $pw=substr($decodage,$pos+1);

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
                    $jsonResp->setData("KO");
                    $jsonResp->setStatusCode(401);
                    $jsonResp->setErrorDescription("Authorization Failed, too many users");
                    return $jsonResp->getJsonResponse();
                    // renvoyer new JsonResponse avec data = $jsonResp->getJsonResponse()
                } else {
                    // creation du access token
                    $at = new AccessToken($userdb, $role, "");
                    $jwt = $at->getAccessToken();
                    $exp = $at->getExpiration();

                    if ( $at->isError() ) {
                        $jsonResp->setData("KO");
                        $jsonResp->setStatusCode(401);
                        $jsonResp->setErrorDescription($at->getErrorDescription());
                        return $jsonResp->getJsonResponse();
                    } else {
                        //writeLog("LoginRestRequest::Access-Token" , (String)$jwt);
                        $rawData  = "OK";
                        //$this->_restResponse->setJwtHeader((string)$jwt);
                        //$this->_restResponse->setExpirationdateHeader($exp);

                        // renvoyer header avec $jwt et $exp
                    }
                } 
            } else {
                //$this->_restResponse->setError();
                //$this->_restResponse->setStatusCode(401);
                //$this->_restResponse->setErrorType('Unknown authorization type');
                //writeLog('LoginRestRequest::', 'Unknown authorization type');
                $rawData  = "KO";
            }

        } else { // chaine authorization null
            //$this->_restResponse->setError();
            //$this->_restResponse->setStatusCode(401);
            //$this->_restResponse->setErrorType('No Basic Authorization');
            $rawData  = "KO";
        }

        //$this->_restResponse->setData($rawData);
        return new JsonResponse(
            $rawData, 
            200
        );
    }
    
}