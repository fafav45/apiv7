<?php

namespace App\Repository;

use PDO;
use App\Entity\Teacher;
use App\Manager\TeacherManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Teacher>
 *
 */
class TeacherRepository extends ServiceEntityRepository
{
    protected $bdd;
    protected $cnx;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Teacher::class);
    }
    
    /**
     * apiGetList
     *
     * @param  mixed $id
     * @param  mixed $usedOnly
     * @return array
     */
    public function apiGetList(int $id=0, $usedOnly=false) : ?array {
        $teacherMgr = new TeacherManager($this->cnx);
        $teacherList = $teacherMgr->apiGetList($id, $usedOnly);
        return $teacherList;
    }
   
    /**
     * apiGetFollowUp
     *
     * @return array
     */
    public function apiGetFollowUp() : ?array {
        $teacherMgr = new TeacherManager($this->cnx);
        $teacherList = $teacherMgr->apiGetFollowUp();
        return $teacherList;
    }
        
    /**
     * apiDuplicates
     *
     * @return array
     */
    public function apiDuplicates() : ?array {
        $teacherMgr = new TeacherManager($this->cnx);
        $teacherList = $teacherMgr->apiDuplicates();
        return $teacherList;
    }   
    
    /**
     * teachersPut
     *
     * @param  int $id
     * @param  string $type
     * @param  mixed $value
     * @param  mixed $typeOf
     * @return int
     */
    public function teachersPut(int $id, string $type, $value, $typeOf) : int {
        $teacherMgr = new TeacherManager($this->cnx);
        $iStatus = $teacherMgr->apiTeacherPut($id, $type, $value, $typeOf);
        return $iStatus;
    }
 
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
