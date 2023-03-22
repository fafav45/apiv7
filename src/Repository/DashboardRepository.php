<?php

namespace App\Repository;

use PDO;
use App\Entity\Dashboard;
use App\Repository\Connexion;
use App\Manager\DashboardManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;



class DashboardRepository extends ServiceEntityRepository
{
    protected $bdd;
    protected $cnx;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dashboard::class);
    }
    

    public function dashboard(Connexion $cnx): ?array
    {
        $manager = new DashboardManager($cnx);

        // Nombre de candidats
        $countcand = $manager->retrieveNombreCandidats($cnx->getBdd()) ;
        // Nombre de profs
        $countprof = $manager->retrieveNombreProfs($cnx->getBdd()) ;
        // Nombre de passages ind
        $countind = $manager->retrieveNombreIndividuels($cnx->getBdd()) ;
        // Nombre de passages duo
        $countduo = $manager->retrieveNombreDuos($cnx->getBdd()) ;
        // Nombre de passages grp
        $countgrp = $manager->retrieveNombreGroupes($cnx->getBdd()) ;
        // nombre de groupes
        $countnbgrp = $manager->retrieveNombreTotalGroupes($cnx->getBdd());
        // nombre ecoles
        $countnbecole = $manager->retrieveNombreEcoles($cnx->getBdd());
        // nombre adhésions candidats
        $countNbCandidateMembership = $manager->retrieveNumberOfAdhesionCandidat($cnx->getBdd());
        // nombre adhésions professeurs
        $countNbTeacherMembership = $manager->retrieveNumberOfAdhesionProf($cnx->getBdd());
        
        $rawData = array('nb_candidates' => $countcand, 'nb_teachers' => $countprof, 'nb_individuals' => $countind, 'nb_duets' => $countduo, 'nb_groups' => $countgrp, 'nb_total_groups' => $countnbgrp, 'nb_schools' => $countnbecole, 'nb_candidate_membership' => $countNbCandidateMembership, 'nb_teacher_membership' => $countNbTeacherMembership);
       
        return $rawData;
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
