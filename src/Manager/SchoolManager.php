<?php
/*
* METHODES
*
* apiGetOne($id) (renvoie une ecole à partir de son id)
* apiGetAll() (renvoie un tableau avec toutes les écoles)
* apiGetFull())
* apiFollowUp()
* setDateConnexion($id)
*
*/

namespace App\Manager;

use PDO;
use App\Repository\Connexion;

class SchoolManager
{
	protected $_db ;
    protected $_logger ;
    protected $_domain = '';
    
	const NEWKEYS=array('nom' => 'name', 'adresse1' => 'address1', 'adresse2' => 'address2', 'ville'=>'city', 'cp'=>'postalCode');

	// constructeur

    public function __construct(Connexion $cnx)
    {
        $this->setDb($cnx->getBdd());
        $this->setDomain($cnx->getSubDomain());
    }
	
	
	/**
	 * apiGetOne return array
	 * @param int $id
	 * @return array
	 */
	public function apiGetOne(int $id) : array {
	    
	    $q = $this->_db->prepare('SELECT
			 `id`,
			 `nom` as name,
			 `adresse1` as address,
			 `adresse2` as additionalAddress,
			 `ville` as city,
			 `cp` as postalCode,
			 `email`,
			 `editdate` as editDate,
			 `validation` as valid,
			 `readonly`,
			 `validationdate` as validationDate,
			 `date_last` as lastConnectionDate
		 	FROM `ecole` 
			WHERE id=:identifiant');

	    $q->bindValue(':identifiant', $id, PDO::PARAM_INT);
	    $q->execute();
	    
	    $tuple= $q->fetchAll(PDO::FETCH_ASSOC) ;

	    return $tuple;
	}
	
	/**
	 * apiGetAll
	 * @param bool $usedOnly
	 * @param bool $countOnly
	 * @return array
	 */
	public function apiGetAll(bool $countOnly=false, bool $usedOnly=true) : array
	{
	    
	    if($countOnly) {

			$sql= "SELECT 
				E.`validation` as 'valid', 
				COUNT(DISTINCT E.`id`) as 'count' 
				FROM `ecole` E
				INNER JOIN  `v_entries` PA
					ON PA.`ecole_id`=E.`id`
				INNER JOIN `user` U 
					ON E.`id`=U.`ecole`
				WHERE U.`isadmin`=0 
				GROUP BY E.`validation`
				ORDER BY validation DESC -- validées puis non validées
			;";
	        
	        $q = $this->_db->prepare($sql);
	        $q->execute();
	        $tuples= $q->fetchAll(PDO::FETCH_ASSOC) ;
	        
	        
	    } else {
	        $tuples = array();
	        
	        // pour $usedOnly=true
	        if ($usedOnly) 

				$sql = "SELECT 
				E.`id`,
				E.`nom` as name,
				E.`adresse1` as address,
				E.`adresse2` as additionalAddress,
				E.`ville` as city,
				E.`cp` as postalCode,
				E.`email`,
				E.`editdate` as editDate,
				E.`validation` as valid,
				E.`readonly`,
				E.`validationdate` as validationDate,
				E.`date_last` as lastConnectionDate 
				FROM `ecole` E 
				INNER JOIN `v_entries` PA 
					ON PA.`ecole_id`=E.`id`
				GROUP BY E.`id`
				ORDER BY E.nom ASC;";

    	    else { //  pour $usedOnly=false
	            $sql="SELECT 
					`id`,
					`nom` as name,
					`adresse1` as address,
					`adresse2` as additionalAddress,
					`ville` as city,
					`cp` as postalCode,
					`email`,
					`editdate` as editDate,
					`validation` as valid,
					`readonly`,
					`validationdate` as validationDate,
					`date_last` as lastConnectionDate
					FROM `ecole` 
					WHERE 1=1
					ORDER BY `nom` ASC";
	        }

	        $q = $this->_db->prepare($sql);
	        $q->execute();
	        $tuples= $q->fetchAll(PDO::FETCH_ASSOC) ;
	        unset($q);unset($sql);
	    }
	    return $tuples;
	}
	
	/**
	 * apiGetFull
	 *
	 * @return array
	 */
	public function apiGetFull() : array
	{
	    
	    $tuples = array();    

		$sql="SELECT 
			`nom` as name,
			`id`,
			`adresse1` as address,
			`adresse2` as additionalAddress,
			`ville` as city,
			`cp` as postalCode,
			`email`,
			`editdate` as editDate,
			`validation` as valid,
			`readonly`,
			`validationdate` as validationDate,
			`date_last` as lastConnectionDate
			FROM `ecole` 
			WHERE 1=1
			ORDER BY `nom` ASC";
	        

	        $q = $this->_db->prepare($sql);
	        $q->execute();
	        $tuples= $q->fetchAll(PDO::FETCH_ASSOC) ;
	        unset($q);unset($sql);

			$sqlUser = "SELECT `username`, `prenom` as 'firstName', `nom` as 'lastName' ,`email`, `date_creation` as 'creationDate', `isadmin`, `actif` as 'active', `editdate` as 'editDate' FROM `user` WHERE ecole=:ID";
			$qUser = $this->_db->prepare($sqlUser);
			$newTuples = array();    

			foreach($tuples as $tuple) {
				$id=$tuple['id'];
				$qUser->bindValue(':ID', $id, PDO::PARAM_INT);
				$qUser->execute();
				$tuplesUser= $qUser->fetchAll(PDO::FETCH_ASSOC) ;
				$tuple['users'] = $tuplesUser;
				array_push($newTuples, $tuple);
			}

	    return $newTuples;
	}
		
	/**
	 * apiFollowUp
	 * 
	 * suivi inscriptions
	 *
	 * @return array
	 */
	public function apiFollowUp() : array
	{
		$sql_select = "SELECT 
		E.`id`,
		E.`nom` as name,
		E.`adresse1` as address,
		E.`adresse2` as additionalAddress,
		E.`ville` as city,
		E.`cp` as postalCode,
		E.`email`,
		E.`editdate` as editDate,
		E.`validation` as valid,
		E.`readonly`,
		E.`validationdate` as validationDate,
		E.`date_last` as lastConnectionDate 
		FROM `ecole` E
		INNER JOIN `v_entries` PA 
			ON PA.`ecole_id`=E.`id`
		INNER JOIN `user` U 
			ON E.`id`=U.`ecole` --
		WHERE 1=1
		AND U.`isadmin`=0
		GROUP BY E.`id`;";
		
		$select_stmt = $this->_db->prepare($sql_select) ;
		$select_stmt->execute() ;
		$tuples = $select_stmt->fetchAll(PDO::FETCH_ASSOC) ;

	    return $tuples;	    
	}
  	
		
	/**
	 * setDb
	 *
	 * @param  PDO $db
	 * @return void
	 */
	public function setDb(PDO $db) : void
	{
		$this->_db = $db;
	}
		

	public function setDomain(string $arg): void
    {
        $this->_domain = $arg;
    }


}