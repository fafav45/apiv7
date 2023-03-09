<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DocController extends AbstractController
{

    public function __construct(
        LoggerInterface $logger)
    {
        $logger->info("documentation Generation");
        spl_autoload_register(array($this, 'autoSwaggerloader'));
        
    }

    public function autoSwaggerloader(string $name) {

        if (file_exists( __SWAGGER__ . $name . '.php')){
            //echo __SWAGGER__ . $name . ".php<br>" . PHP_EOL;
            require_once __SWAGGER__ . $name . '.php';
        }
        else if (file_exists( __API__ . $name . '.php')){
            //echo __API__ . $name . '.php<br>' . PHP_EOL;
            require_once __API__ . $name . '.php';
        }
        else if (file_exists(__ENTITY__ . $name . '.php')){
            //echo __ENTITY__ . $name . '.php<br>' . PHP_EOL;
            require_once __ENTITY__ . $name . '.php';
        }
        else if (file_exists(__CONTROLLER__ . $name . '.php')){
            //echo __CONTROLLER__ . $name . '.php<br>' . PHP_EOL;
            require_once __CONTROLLER__ . $name . '.php';
        }
    }

    public function index(): Response
    {
        define("__ROOT__", $_SERVER['DOCUMENT_ROOT'].'/api/');
        define("__VERSION__",'v7/');
        define("__API__", __ROOT__ . __VERSION__);
        define("__ENTITY__", __API__ . 'entity/');
        define("__CONTROLLER__", __API__ . 'controller/');
        define("__SWAGGER__", __API__ . 'swagger/');
        define("__OUTPUT__", __SWAGGER__ . 'swagger.json');

        require(__ROOT__."/vendor/autoload.php");

        $exclude = ['EventSubscriber','Repository','Manager','Security','Kernel.php','DocController.php','sqlFunctions.php','currentSecret.php','Connexion.php'];
        $pattern = '*.php';
        // scan recursif à partir du finder, à partir de /api/v7/src
        $openapi = \OpenApi\Generator::scan(\OpenApi\Util::finder([__API__ . 'src/'], $exclude, $pattern));

        $myJSON = $openapi->toJSON();

        // SAUVEGARDE dans Dossier
        //echo '<br>Output: ' . __OUTPUT__ . '<br>' . PHP_EOL;

        file_put_contents(__OUTPUT__ ,  $myJSON);

        return new Response(
            "doc has been generated in " . __OUTPUT__ ,
            Response::HTTP_OK,
            []
        );

        /*
        * from v7
        * ./vendor/bin/openapi src/entity src/controller -f json -o -o ./swagger/swagger.json
        */
    }


}
