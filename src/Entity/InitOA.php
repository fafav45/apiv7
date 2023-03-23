<?php

namespace App\Entity;

use OpenApi\Annotations as OA;

class InitOA {

/**
 * @OA\Info(
 *     version="1.0",
 *     title="CND Rest API", version="7.0"
 * )
 * @OA\Server(
 *      url="http://ins-2023.aml.fr/api/v7",
 *      description="REST APIs CND"
 * )
 * @OA\Server(
 *      url="https://ins-demo.cnd.info/api/v7",
 *      description="Site Demo"
 * )
  * @OA\Server(
 *      url="https://ins-bs.obamalou.fr/api/v7",
 *      description="Site Demo"
 * )
 * @OA\ExternalDocumentation(
 *     description="Find out more about Swagger",
 *     url="http://swagger.io"
 * )
 */
    public function getInitOA() {
        // nothing
    }
}

?>