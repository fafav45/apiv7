<?php

namespace App\Manager;

use PDO;
use App\Repository\Connexion;

class DashboardManager
{
    private $_db ;

    private $_domain = '';

    // constructeur

    public function __construct(Connexion $cnx)
    {
        $this->setDb($cnx->getBdd());
        $this->setDomain($cnx->getSubDomain());
    }

    /**
	 * retrieveNombreCandidats
	 *
	 * @param  mixed $bdd
	 * @return int
	 */
	function retrieveNombreCandidats(PDO $bdd) : int
	{
		$sql_count = "SELECT COUNT(DISTINCT `candidat_id`) from `v_cand_grp`;";
		$count_stmt = $bdd->prepare($sql_count) ;
		$count_stmt->execute() ;
		$var = $count_stmt->fetchColumn();
		return((int)$var) ;
	}

    /**
	 * retrieveNombreProfs
	 *
	 * @param  mixed $bdd
	 * @return int
	 */
	function retrieveNombreProfs(PDO $bdd) : int
	{
		$sql_count="SELECT COUNT(DISTINCT `prof_id`) FROM `v_entries` WHERE `prof_id`<>9999";
		$count_stmt = $bdd->prepare($sql_count) ;
		$count_stmt->execute() ;
		$var = $count_stmt->fetchColumn();
		return((int)$var) ;
	}


    /**
	 * retrieveNombreIndividuels
	 *
	 * @param  mixed $bdd
	 * @return int
	 */
	function retrieveNombreIndividuels(PDO $bdd) : int
	{
		$sql_count = "SELECT COUNT(*) from `v_entries` WHERE `nature_id`=1" ;
		$count_stmt = $bdd->prepare($sql_count) ;
		$count_stmt->execute() ;
		return (int)$count_stmt->fetchColumn();
	}

    /**
	 * retrieveNombreDuos
	 *
	 * @param  mixed $bdd
	 * @return int
	 */
	function retrieveNombreDuos(PDO $bdd) : int
	{
		$sql_count="SELECT COUNT(*) from `v_entries` WHERE `nature_id`=2";
		$count_stmt = $bdd->prepare($sql_count) ;
		$count_stmt->execute() ;
		return (int)$count_stmt->fetchColumn();
	}


	/**
	 * retrieveNombreGroupes
	 *
	 * @param  mixed $bdd
	 * @return int
	 */
	function retrieveNombreGroupes(PDO $bdd) : int
	{
		$sql_count="SELECT COUNT(*) from `v_entries` WHERE `nature_id`=3";
		$count_stmt = $bdd->prepare($sql_count) ;
		$count_stmt->execute() ;
		return (int)$count_stmt->fetchColumn();
	}


	/**
	 * retrieveNombreTotalGroupes
	 *
	 * @param  mixed $bdd
	 * @return int
	 */
	function retrieveNombreTotalGroupes(PDO $bdd) : int 
	{
		$sql="SELECT count(*) FROM `v_cand_grp` CG
		WHERE CG.`nature_id`=3";
		$q = $bdd->prepare($sql) ;
		$q->execute() ;
		return (int)$q->fetchColumn();
	}

	/**
	 * retrieveNombreEcoles
	 *
	 * @param  mixed $bdd
	 * @return int
	 */
	function retrieveNombreEcoles(PDO $bdd) : int
	{
		$sql_count="SELECT COUNT(DISTINCT E.`id`) FROM `v_entries` PA
		INNER JOIN `ecole` E
			ON PA.`ecole_id`=E.`id`
		WHERE PA.`prof_id`<>9999;";
		$count_stmt = $bdd->prepare($sql_count) ;
		$count_stmt->execute() ;
		return (int)$count_stmt->fetchColumn();
	}

    /**
	 * retrieveNumberOfAdhesionCandidat
	 *
	 * @param  mixed $bdd
	 * @return int
	 */
	function retrieveNumberOfAdhesionCandidat(PDO $bdd) : int {
		$sql_count = 'SELECT COUNT(`id`) FROM `candidat` WHERE `cnd`=1 -- comment';
		$count_stmt = $bdd->prepare($sql_count) ;
		$count_stmt->execute() ;
		$var = $count_stmt->fetchColumn();
		return((int)$var) ;
	}

	/**
	 * retrieveNumberOfAdhesionProf
	 *
	 * @param  mixed $bdd
	 * @return int
	 */
	function retrieveNumberOfAdhesionProf(PDO $bdd) : int
	{
		$sql_count = 'SELECT COUNT(`id`) FROM `professeur` WHERE `cnd`=1 -- comment';
		$count_stmt = $bdd->prepare($sql_count) ;
		$count_stmt->execute() ;
		$var = $count_stmt->fetchColumn();
		return((int)$var) ;
	}


    /**
     * @param PDO $db
     */
    public function setDb(PDO $db)
    {
        $this->_db = $db;
    }

    /**
     * setDomain
     *
     * @param  string $arg
     * @return void
     */
    public function setDomain(string $arg): void
    {
        $this->_domain = $arg;
    }
}
