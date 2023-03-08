<?php

namespace App\Entity;

use OpenApi\Annotations as OA;

/**
* @OA\Schema(     
 * schema="Error",
 * title="Error",   
*  @OA\Property(property="Error", type="array",
*    @OA\Items(type="object",
*        @OA\Property(property="type", type="string"),
*        @OA\Property(property="description", type="string")
*    )
*  )
*)
*
* @OA\Schema(     
 * schema="Common",
 * title="Common",   
 * @OA\Property(property="Common", type="array",
 *    @OA\Items(type="object",
 *      @OA\Property(property="method", type="string"),
 *      @OA\Property(property="id", type="integer"),
 *      @OA\Property(property="value", type="string"),
 *      @OA\Property(property="count", type="integer"),
 *      @OA\Property(property="type", type="integer")
 *    )
 *  )
 *)
 *
 * @OA\Schema(
 * schema="Header",
 * title="Header",
 *  @OA\Property(property="objectType", type="string"), 
 *  @OA\Property(property="statusCode", type="integer"),
 *  @OA\Property(property="statusMessage", type="string")
 * )
 * 
 * @OA\Schema(
 *  schema="Response",
 * 	title="Response",
 *  allOf={
 *      @OA\Schema(ref="#/components/schemas/Header"),
 *      @OA\Schema(ref="#/components/schemas/Error"),
 *      @OA\Schema(ref="#/components/schemas/Common")
 *  }
 * )
 * 
 */
class Response {}
 ?>