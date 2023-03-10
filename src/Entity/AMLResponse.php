<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\Request;

class AMLResponse
{
    protected $id=null;
    protected $data=null;
    protected $count=0;
    protected $objectType="unknown";
    protected $method="unknown";
    protected $link="unknown";
    protected $value=null;
    protected $type=null;
    protected $errorType=null;
    protected $errorDescription=null;
    protected $statusCode=200;
    protected $context;
    protected $useragent;


    public function __construct(Request $request)
    {
        $this->link = $request->getPathInfo();
        $this->method = $request->getMethod();
    }

    public function getJsonResponse(): array
    {
        $common = array("_link" => $this->link, "method" => $this->method, "id" => $this->id, "value" => $this->value, "count" => $this->count, "type" => $this->type);
        $errors = array("type"=> $this->errorType, "description"=> $this->errorDescription);
        $ret = array("objectType" => $this->objectType, "statusCode" => $this->statusCode, $this->objectType => $this->data, "common" => $common, "errors" => $errors);
        return $ret;
    }


    public function setId(int $id): void
    {
        $this->id = $id;
    }


    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * Set the value of count
     *
     * @return  void
     */
    public function setCount(int $count): void
    {
        $this->count = $count;
    }


    public function setObjectType(string $objectType): void
    {
        $this->objectType = $objectType;
    }

    /**
     * Set the value of errorDescription
     *
     * @return  void
     */
    public function setErrorDescription(string $errorDescription): void
    {
        $this->errorDescription = $errorDescription;
    }


    public function setErrorType(string $errorType): void
    {
        $this->errorType = $errorType;
    }

    /**
     * Set the value of statusCode
     *
     * @return  void
     */    
    /**
     * setStatusCode
     *
     * @param  mixed $statusCode
     * @return void
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }
    
    /**
     * getStatusCode
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return (int)$this->statusCode;
    }


    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * Get the value of context
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * Set the value of context
     *
     * @return  self
     */
    public function setContext(string $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get the value of useragent
     */
    public function getUseragent(): string
    {
        return $this->useragent;
    }

    /**
     * Set the value of useragent
     *
     * @return  self
     */
    public function setUseragent(string $useragent)
    {
        $this->useragent = $useragent;

        return $this;
    }

    private function processContext(string $uag): string
    {

        /* a mettre dans LoginController qui contient $request
        * ou mieux faire un extend de LoginController vers RequestController
        */

        $moteur = array('Gecko/','AppleWebKit/','Opera/','Trident/','Chrome/','Chromium/','Safari/','MSIE ','Opera/', 'OPR/');
        // tentative de dermination du moteur de rendu
        $context = $uag;
        foreach ($moteur as $a) {
            if (stripos($uag, $a) !== false) {
                $context = 'web';
                break;
            }
        }
        // idéalement, il nous faudrait $request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            $context = 'ajax';
        }

        return $context;
    }

    /**
     * Get the value of type
     */ 
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */ 
    public function setType($type) : void
    {
        $this->type = $type;
    }
}
