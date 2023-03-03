<?php

use OA\Info;
use OA\Server;
use OA\ExternalDocumentation;
use OpenApi\Annotations as OA;

class InitOA {

/**
 * @Info(
 *     version="1.0",
 *     title="CND Rest API", version="6.0"
 * )
 * @Server(
 *      url="http://ins-2023.aml.fr/api/v6",
 *      description="REST APIs CND"
 * )
  * @OA\Server(
 *      url="https://ins-demo.cnd.info/api/v6",
 *      description="Site Demo"
 * )
 * @ExternalDocumentation(
 *     description="Find out more about Swagger",
 *     url="http://swagger.io"
 * )
 */
    public function getInitOA() {
        // nothing
    }
}

?>