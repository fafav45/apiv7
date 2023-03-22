<?php

namespace App\Repository;

use PDO;
use App\Manager\FileManager;
use Psr\Log\LoggerInterface;


//class DownloadRepository extends ServiceEntityRepository
class DownloadRepository
{
    protected $bdd;
    protected $cnx;
    protected $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }


    public function downloadGet(Connexion $cnx, $id) {
        $this->logger->info("repository downloadGet"); //ok
        $this->logger->info("id=$id"); // ok
        $manager = new FileManager($cnx) ;
        $myFichiers = $manager->apiGet($id, false); // array

        if (!is_null($myFichiers)) {
            if(isset($myFichiers[0])) {
                $myFichier = $myFichiers[0];
            } else {
                $myFichier = null;
            }
        } else {
            $myFichier = null;
        }

        $myFichier = is_null($myFichiers)?null:$myFichiers[0];
        // $this->logger->info("myFichier=".json_encode($myFichier)); // ok
        // myFichier=[{"id":"2","school_id":"1","candidate_id":"0","uniq_id":"DUO14_6318875748e2a.MP3","name":"DUO14.MP3","type":"audio\/mpeg","date":"2022-09-07","docType":"6","entry_id":"14","teacher_id":"0","md5":null}]

        $returnArray = array("file" => "", "name"=>"", "size"=>0, "error"=>"");

        if (!is_null($myFichier)) {
            //$this->logger->info("myFichier not null");
            //$this->logger->info("name: " . $myFichier["name"]); 
            //$this->logger->info("uniq_id: " . $myFichier["uniq_id"]);
            
            $file = $cnx->getMyrootDir() . DIRECTORY_SEPARATOR . "Files" . DIRECTORY_SEPARATOR . $cnx->getSubDomain() . DIRECTORY_SEPARATOR . $myFichier["uniq_id"] ;
            $name = $myFichier["name"];
            $returnArray["file"] = $file;
            $returnArray["name"] = $myFichier["name"];

            if (file_exists($file)) {
                //$this->logger->info("File exists");
                $size = filesize($file);
                $returnArray["size"] = filesize($file);
            } else {
                $this->logger->error("DownloadRepository::File does not exist");
                $returnArray["error"] = "File does not exist";
            }

        } else {
            $this->logger->error("DownloadRepository::File not found in database");
            $returnArray["error"] = "File not found in database";
        }
        return $returnArray;
    }

    public function setBdd(PDO $arg)
    {
        $this->bdd = $arg;
    }

    public function setConnexion(Connexion $arg)
    {
        $this->cnx = $arg;
    }


}
