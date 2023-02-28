<?php

namespace App\Repository;

use PDO;
use App\Repository\Connexion;
use App\Security\AccessToken;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


class LoginRepository extends ServiceEntityRepository
{
    protected $bdd;
    protected $cnx;
    protected $user;
    protected $password;

    protected $authorization;

    public function __construct(ManagerRegistry $registry)
    {
 
    }

    public function login() {

        $userdb="";
        $role="";
        // if (strtoupper($name) == 'AUTHORIZATION') { // seulement pour login
        //     $this->authorization = $value;
        // }

        // creation du access token
        $at = new AccessToken($userdb, $role);
        $jwt = $at->getAccessToken();
        $exp = $at->getExpiration();

        $chaine = $this->getAuthorization();
    }

    protected function getAuthorization() { 
        return $this ->authorization;
    }
    public function setBdd(PDO $arg) {
        $this->bdd = $arg;
    }

    public function setConnexion(Connexion $arg) {
        $this->cnx = $arg;
    }

}