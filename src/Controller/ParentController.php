<?php

namespace App\Controller;

use App\Entity\AMLResponse;
use App\Security\AccessToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ParentController
{
    protected $connexion; // Connexion
    protected $accessToken; // string JWT
    protected $logger;
    protected $custoResponse; // AMLResponse

    public function __construct()
    {
        if (!defined('SECRET')) {
            define("SECRET", "ed8e871108709b93b0b200ddf19b11be14c417e75efed9d21078efe6efef4880");
        }
        $request = Request::createFromGlobals();
        $this->custoResponse = new AMLResponse($request);
    }

    protected function getAccessToken(): ?AccessToken
    {
        return $this ->accessToken;
    }

    protected function getApiContext(Request $request): string
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
    } // end function
    
    /**
     * isApiAuthorized
     *
     * @param  mixed $context
     * @param  mixed $at
     * @return string return non empty string with description if not authorized
     */
    protected function isApiAuthorized(?string $context, ?string $at) : string
    {
        $desc = "";
        if ($context === 'ajax') {
            $desc = "";
        } elseif (is_null($at)) {
            $desc = 'Access Token is missing';
        } else { // access_token non null, on doit le vérifier
            $oat = new AccessToken($at);
            if ($oat->hasError()) {
                $desc = $oat->getErrorDescription();
            }
        }
        return (string)$desc;
    } // end function


    
    /**
     * returnNotAuthorized
     *
     * @param  mixed $text
     * @return JsonResponse Returns a Jsonresponse of no authorized access
     */
    protected function returnNotAuthorized(string $text): JsonResponse
    {
        $this->custoResponse->setCount(0);
        $this->custoResponse->setErrorDescription($text);
        $this->custoResponse->setErrorType("TOKEN");
        $this->custoResponse->setStatusCode(Response::HTTP_UNAUTHORIZED);
        $jsonResponse = $this->custoResponse->getJsonResponse();

        $response = new JsonResponse($jsonResponse, Response::HTTP_UNAUTHORIZED, array(), false); // content, status, headers, false if already json
        return $response;
    } // end function
}
