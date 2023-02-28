<?php

namespace App\Repository;

use PDO;
use App\Entity\File;
use App\Manager\FileManager;
use App\Repository\Connexion;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<File>
 *
 * @method File|null find($id, $lockMode = null, $lockVersion = null)
 * @method File|null findOneBy(array $criteria, array $orderBy = null)
 * @method File[]    findAll()
 * @method File[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileRepository extends ServiceEntityRepository
{
    protected $bdd;
    protected $cnx;

    public function __construct(ManagerRegistry $registry)
    {
        //public function __construct(ManagerRegistry $registry)
        
        parent::__construct($registry, File::class);
        //$cnx est l'objet Connexion, definition dans FileController
        //$this->fm = $fm;
        //$this->setBdd($fm->getBdd());
        
    }

    public function findAllFiles(Connexion $cnx) : ?array{

        $fileMgr = new FileManager($cnx);
        $ar = $fileMgr->apiGetAll();

        return $ar;
    }

    public function findAllTest(): array
    {
        return array(array("id"=>"1", "name"=>"tata"),array("id"=>"2", "name"=>"titi"));
    }
    
    public function findOneFile(Connexion $cnx, int $id, bool $withMD5): array
    {
        $fileMgr = new FileManager($cnx);
        $ar = $fileMgr->apiGet($id, $withMD5);

        return $ar;
    }


    public function setBdd(PDO $arg) {
        $this->bdd = $arg;
    }

    public function setConnexion(Connexion $arg) {
        $this->cnx = $arg;
    }

}
