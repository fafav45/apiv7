<?php

namespace App\Security;

//use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;

class AccessToken
{
    private $_tokenParts = null;
    private $_accessToken = null;
    private $_header = null;
    private $_payload = null;
    private $_signature = null;
    private $_signatureProvided = null;

    private $_header64 = null;
    private $_payload64 = null;
    private $_signature64 = null;

    private $_expiration;
    private $_user = null;
    private $_role;
    private $_context = null;

    private $_tokenExpired = false;
    private $_signatureIsValid = true;

    private $_error = false;
    private $_errorType;
    private $_errorDescription = null;




    //public function __construct(LoggerInterface $logger)
    public function __construct()
    {
        // à mettre ailleurs ?
        //$logger->info("AccessToken construct");

        if (!defined('SECRET')) {
            define("SECRET", "ed8e871108709b93b0b200ddf19b11be14c417e75efed9d21078efe6efef4880");
        }

        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this, $f='__construct'.$i)) {
            call_user_func_array(array($this,$f), $a);
        }
    }

    public function __construct0()
    {
        writeLog('AccessToken', 'null');
    }

    public function __construct3(String $user, String $role, String $context)
    {

        //writeLog('AccessToken', "user: $user , role: $role , context: $context");
        $this->_user = $user;
        $this->_role = $role;
        $this->_context = $context;
 
        //$logger->info("New access token for $user");

        // Les horodatages Unix ne contiennent aucune information concernant le fuseau horaire local
        // c'est l'heure  GMT

        $this->_expiration = 3600 + time();

        /*
        expiration : 3600 en standard (1 heure)
        si excel, 3600 * 72 = (3 jours)
        */
        if ($context === "MS-Excel") {
            $this->_expiration = 259200 + time();
        } // 3 jours

        $this->_header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);

        $this->_payload = json_encode([
            'user' => $user,
            'role' => $role, // not yet implemented
            'expiration' => $this->_expiration,
            'context' => $context // ou java, ou excel
        ]);

        // dans le cas d'un refresh, seul le payload doit-être recalculé
        // et la signature

        // Encode Header
        $this->_header64 = $this->base64UrlEncode($this->_header);

        // Encode Payload
        $this->_payload64 = $this->base64UrlEncode($this->_payload);

        // Create Signature Hash
        $this->_signature = hash_hmac('sha256', $this->_header64 . "." . $this->_payload64, constant("SECRET"), true);

        // Encode Signature to Base64Url String
        $this->_signature64 = $this->base64UrlEncode($this->_signature);

        // Create JWT
        $jwt = $this->_header64 . '.' . $this->_payload64 . '.' . $this->_signature64;

        // log it
        //write_log('JWT: ' . $jwt);
        //$this->writeLog('AccessToken::JWT', $jwt);
        //writeLog('AccessToken::JWT', $jwt);

        $this->_accessToken = (string)$jwt;
    }

    /**
     * __construct1
     *
     * @param  string $accessToken
     * @return void
     */
    public function __construct1(String $accessToken)
    {
        $this->_accessToken = $accessToken;
        $this->_tokenParts = explode('.', $this->_accessToken);
        $countParts = count($this->_tokenParts);

        if ($countParts == 3) {
            $this->_header = base64_decode($this->_tokenParts[0]);
            $this->_payload = base64_decode($this->_tokenParts[1]);
            $this->_signatureProvided = $this->_tokenParts[2];

            // retrieve information from JWT payload
            $this->_expiration = json_decode($this->_payload)->expiration;
            $this->_user       = json_decode($this->_payload)->user;
            $this->_role       = json_decode($this->_payload)->role;
            $this->_context    = json_decode($this->_payload)->context;

            // build a signature based on the header and payload using the secret
            $this->_header64 = $this->base64UrlEncode($this->_header);
            $this->_payload64 = $this->base64UrlEncode($this->_payload);

            $this->_signature = hash_hmac('sha256', $this->_header64 . '.' . $this->_payload64, constant("SECRET"), true);
            $this->_signature64 = $this->base64UrlEncode($this->_signature);

            // verify it matches the signature provided in the token
            $this->_signatureIsValid = ($this->_signature64 === $this->_signatureProvided);

            if ($this->_signatureIsValid) {
                //writeLog('AccessToken::signature', 'valid');
            } else {
                //writeLog('AccessToken::signature', 'NOT valid');
                $this->setError();
                $this->_errorDescription    = 'Access Token not valid';
            }

            $this->_tokenExpired=($this->_expiration > time()) ? false : true;

            if ($this->_tokenExpired) {
                //writeLog('AccessToken::Access-Token', 'EXPIRED');

                $this->setError();
                //$this->_statusCode          = 401;
                $this->_errorType           = 'Token';
                $this->_errorDescription    = 'Access Token expired';
            } else {
                $toto = $this->_expiration - time();
                //writeLog('AccessToken::Access-Token', 'expires in ' . $toto . ' seconds');
            }

            /* log payload information */
            //if (!is_null($this->_user))
            //writeLog('AccessToken::user', $this->_user);
            //if (!is_null($this->_role))
            //    writeLog('AccessToken::role', $this->_role);
            //if (!is_null($this->_context))
            //    writeLog('AccessToken::context', $this->_context);
        } else {
            //writeLog('AccessToken::Access-Token', 'format NOT valid');
            $this->setError();
            $this->_errorDescription    = 'Access Token format not valid';
        }
    }

    public function refresh(String $accessToken): string
    {
        $this->_accessToken = $accessToken;
        $this->_tokenParts = explode('.', $this->_accessToken);
        $countParts = count($this->_tokenParts);

        if ($countParts == 3) {
            $this->_header64 = $this->_tokenParts[0];
            $this->_payload64 = $this->_tokenParts[1];
            $this->_signatureProvided = $this->_tokenParts[2];

            $this->_payload = base64_decode($this->_payload64);

            $this->_signature = hash_hmac('sha256', $this->_header64 . '.' . $this->_payload64, constant("SECRET"), true);
            $this->_signature64 = $this->base64UrlEncode($this->_signature);

            // on verifie que lasignature actuelle est bonne
            $this->_signatureIsValid = ($this->_signature64 === $this->_signatureProvided);

            if ($this->_signatureIsValid) {
                // on continue

                $this->_user       = json_decode($this->_payload)->user;
                $this->_role       = json_decode($this->_payload)->role;
                $this->_context    = json_decode($this->_payload)->context;

                $this->_expiration = 3600 + time();
                // expiration : 3600 en standard (1 heure). si excel, 3600 * 72 = (3 jours)
                if ($this->_context === "MS-Excel") {
                    $this->_expiration = 259200 + time();
                }
                //$this->_expiration

                // on recreer le payload
                $this->_payload = json_encode([
                    'user' => $this->_user,
                    'role' => $this->_role, // not yet implemented
                    'expiration' => $this->_expiration,
                    'context' => $this->_context // ou java, ou excel
                ]);

                // on recalcule payload 64 et signature 64
                $this->_payload64 = $this->base64UrlEncode($this->_payload);

                // et signature 64
                $this->_signature = hash_hmac('sha256', $this->_header64 . '.' . $this->_payload64, constant("SECRET"), true);
                $this->_signature64 = $this->base64UrlEncode($this->_signature);

                // Create JWT
                $jwt = $this->_header64 . '.' . $this->_payload64 . '.' . $this->_signature64;

                // log it
                //writeLog('AccessToken::refresh JWT', $jwt);
                $this->_accessToken = (string)$jwt;
            } else {
                //writeLog('AccessToken::signature', 'NOT valid');
                $this->setError();
                $this->_errorDescription    = 'Access Token not valid';
                $this->_accessToken = null;
            }
        } else {// si 3 part
            $this->_accessToken = null;
        }

        return $this->_accessToken;
    }


    /**
     * base64UrlEncode
     *
     * @param  string $text
     * @return string
     */
    private function base64UrlEncode($text): ?string
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }

    /**
     * getAccessToken
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        return (string)$this ->_accessToken;
    }

    /**
     * getExpiration
     *
     * @return int
     */
    public function getExpiration(): int
    {
        return $this->_expiration;
    }

    /**
     * getRole
     *
     * @return int
     */
    public function getRole(): int
    {
        return $this ->_role;
    }

    /**
     * getUser
     *
     * @return string
     */
    public function getUser(): ?String
    {
        return $this ->_user;
    }

    /**
     * getContext
     *
     * @return string context(web, MS Excel, Postman)
     */
    public function getContext(): ?String
    {
        return $this ->_context;
    }

    /**
     * isTokenExpired
     *
     * @return bool
     */
    public function isTokenExpired(): bool
    {
        return $this ->_tokenExpired;
    }

    /**
     * isSignatureValid
     *
     * @return bool
     */
    public function isSignatureValid(): bool
    {
        return $this->_signatureIsValid;
    }

    public function isError(): bool
    {
        return $this->_error;
    }

    /**
     * hasError
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->_error;
    }

    protected function setError()
    {
        $this->_error = true;
        ;
    }

    public function getErrorDescription(): ?String
    {
        return $this ->_errorDescription;
    }
}
