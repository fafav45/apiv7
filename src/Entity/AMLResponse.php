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


    public function __construct(Request $request) {
        $this->link = $request->getPathInfo();
        $this->method = $request->getMethod();
    }

    public function getJsonResponse() : array {
        $common = array("_link" => $this->link, "method" => $this->method, "id" => $this->id, "value" => $this->value, "count" => $this->count, "type" => $this->type);
        $errors = array("type"=> $this->errorType, "description"=> $this->errorDescription);
        $ret = array("objectType" => $this->objectType, $this->objectType => $this->data, "common" => $common, "errors" => $errors);
        return $ret;
    }

    public function setId(int $id) : void
    {
        $this->id = $id;
    }


    public function setData(mixed $data) : void
    {
        $this->data = $data;
    }

    /**
     * Set the value of count
     *
     * @return  void
     */ 
    public function setCount(int $count) : void
    {
        $this->count = $count;
    }


    public function setObjectType(string $objectType) : void
    {
        $this->objectType = $objectType;
    }

    /**
     * Set the value of errorDescription
     *
     * @return  void
     */ 
    public function setErrorDescription(string $errorDescription) : void
    {
        $this->errorDescription = $errorDescription;
    }


    public function setErrorType(string $errorType) : void
    {
        $this->errorType = $errorType;
    }

    /**
     * Set the value of statusCode
     *
     * @return  void
     */ 
    public function setStatusCode(int $statusCode) : void
    {
        $this->statusCode = $statusCode;
    }
}

?>