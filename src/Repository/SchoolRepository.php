<?php

namespace App\Repository;

use PDO;
use App\Entity\School;
use App\Manager\SchoolManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


//class SchoolRepository extends ServiceEntityRepository
class SchoolRepository
{
    protected $bdd;
    protected $cnx;

    // public function __construct(ManagerRegistry $registry)
    // {
    //     parent::__construct($registry, School::class);
    // }
    
    /**
     * findAllSchools
     *
     * @param  mixed $cnx
     * @param  mixed $countOnly
     * @param  mixed $usedOnly
     * @return array
     */
    public function findAllSchools(Connexion $cnx, bool $countOnly=false, bool $usedOnly=false): ?array
    {
        $schoolMgr = new SchoolManager($cnx);
        $ar = $schoolMgr->apiGetAll($countOnly, $usedOnly);

        return $ar;
    }
    
    /**
     * findAllSchoolsFull
     *
     * @param  mixed $cnx
     * @return array
     */
    public function findAllSchoolsFull(Connexion $cnx): ?array
    {
        $schoolMgr = new SchoolManager($cnx);
        $ar = $schoolMgr->apiGetFull();

        return $ar;
    }

    
    /**
     * schoolsFollowUp
     *
     * @param  mixed $cnx
     * @return array
     */
    public function schoolsFollowUp(Connexion $cnx): ?array
    {
        $schoolMgr = new SchoolManager($cnx);
        $ar = $schoolMgr->apiFollowUp();

        return $ar;
    }
    
    /**
     * findOneSchool
     *
     * @param  mixed $cnx
     * @param  mixed $id
     * @return array
     */
    public function findOneSchool(Connexion $cnx, int $id): ?array
    {
        $schoolMgr = new SchoolManager($cnx);
        $ar = $schoolMgr->apiGetOne($id);
        return $ar;
    }
    
    /**
     * setConnexion
     *
     * @param  mixed $arg
     * @return void
     */
    public function setConnexion(Connexion $arg)
    {
        $this->cnx = $arg;
    }
    
    /**
     * setBdd
     *
     * @param  mixed $arg
     * @return void
     */
    public function setBdd(PDO $arg)
    {
        $this->bdd = $arg;
    }

}
