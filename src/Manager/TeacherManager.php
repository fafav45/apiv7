<?php

namespace App\Manager;

use PDO;
use App\Repository\Connexion;

class TeacherManager
{

	protected $_db ;
    protected $_domain = '';


	const NEWKEYS=array('ecole_id' => 'school_id', 'categorie_id' => 'category_id',
	'classe_id'=>'class_id', 'pistecd_id'=>'track',
	'duree'=>'duration', 'id_candidat'=>'candidate_id',
	'titre'=>'title','morceau'=>'song',
	'prof_id'=>'teacher_id','CD'=>'receivedMP3',
	'mp3'=>'validatedMP3','auteur'=>'author',
	'nom'=>'lastName','prenom'=>'firstName',
	'date_naissance'=>'birthday','naissance'=>'birthday',
	'adresse1'=>'address1','adresse2'=>'additionalAddress',
	'ville'=>'city','cp'=>'postalCode',
	'telephone1'=>'phoneNumber1','telephone2'=>'phoneNumber2',
	'id_ecole'=>'school_id','ecole'=>'schoolName',
	'sexe'=>'gender',
	'atmin'=>'minorCertificate','atmaj'=>'majorCertificate',
	'valide'=>'validatedBirthday','photo'=>'validatedPhoto',
	'cnd'=>'cndMember','adhbureau'=>'cndBoardMember',
	'carte'=>'sentCard','empreinte'=>'digitalPrint',
	'ecole_cp'=>'schoolPostalCode','pcnd'=>'cndPaid',
	'pind'=>'indPaid','pduo'=>'duoPaid', 'pgrp'=>'grpPaid',
	'editdate'=>'editDate','telephone'=>'phoneNumber',
	'fileusage'=>'nbFiles');
	
	// constructeur

	public function __construct(Connexion $cnx)
	{
        $this->setDb($cnx->getBdd());
        $this->setDomain($cnx->getSubDomain());
	}

	
	/**
	 * delete. fonction pas forcemment utilisée, prog detruit par api (DEL vx/teachers/{id} )
	 *
	 * @param  int $id
	 * @return bool
	 */
	public function apiDelete(int $id) : bool
	{
		if (!is_numeric($id)) {
			trigger_error('ProfManager::delete : la valeur reçue n\'est pas de type entier', E_USER_WARNING);
			return 0;
		}
		$sql = "DELETE FROM `professeur` WHERE `id`=:ID";
		$q = $this->_db->prepare($sql);
		$q->bindValue(':ID', $id, PDO::PARAM_INT);
		return (bool) $q->execute();
	}
	
	/**
	 * apiGetList
	 *
	 * @param  mixed $id
	 * @return array
	 */
	public function apiGetList(int $id=0, bool $usedOnly=false) : ?array
	{
		if ($id !== 0 && is_numeric($id)) {
			$sql_where=" AND T.`id`=$id";
		} else {
			$sql_where='';
		}

		if($usedOnly) {
			$sql_usedOnly="INNER JOIN `v_entries` PA 
				ON PA.`prof_id`=E.`id`";
		} else {
			$sql_usedOnly='';
		}

		$sql="SELECT T.*, E.`nom` as schoolName, count(F.`id`) as nbFiles FROM `professeur` T
                LEFT JOIN `fichiers` F 
                    ON T.`id`=F.`prof_id`
				INNER JOIN `ecole` E
					ON E.`id`=T.`id_ecole`
				$sql_usedOnly
				WHERE 1=1 $sql_where
                GROUP BY T.`id`
                ORDER BY T.`nom`, T.`prenom`";
		
		$q = $this->_db->prepare($sql);
		$q->execute();
		$tuples = $q->fetchAll(PDO::FETCH_ASSOC) ;

		return $this->replaceKeys($tuples, self::NEWKEYS);
	
	}

	/**
	 * getFollowUp. return array for suivi_inscriptions
	 *
	 * @return array
	 */
	public function apiGetFollowUp() : array
	{
		$sql = "SELECT 
		T.`id`,
		T.`nom`,
		T.`prenom`,
		T.`ecole`,
		T.`cpe` as 'schoolPostalCode',
		T.`pcnd` as 'cndPaid',
		T.`carte` as 'sentCard',
		T.`empreinte` as 'digitalPrint',
		T.`photo` as 'validatedPhoto',
		T.`cni` as 'validatedBirthday',
		T.`comment`,
		T.`dejapaye` as 'alreadyPaid',
		F1.`id` as 'cni_id',
		F2.`id` as 'photo_id',
		F1.`nom` as 'cniName',
		F2.`nom` as 'photoName'
		from
		(
			(SELECT 
			P.`id`, P.`nom`, P.`prenom`, E.`nom` AS ecole, E.`cp` AS cpe, P.`pcnd`,P.`carte`,P.`empreinte`, P.`photo`, P.`valide` AS 'cni', P.`comment`,
			CASE   
				WHEN CAST(P.`pcnd` AS DECIMAL) > 0 THEN 0
				ELSE 1
			END as 'dejapaye'
			FROM `professeur` P
			INNER JOIN `ecole`E
				ON E.`id` = P.`id_ecole`
				AND `empreinte` IN (
					SELECT P.`empreinte` FROM `professeur` P
					WHERE  P.`cnd`=1
					GROUP BY `empreinte`
					having count(P.`empreinte`) > 1
				)
				AND P.`cnd`=1
			)
		UNION
			(SELECT 
			P.`id`, P.`nom`, P.`prenom`, E.`nom` AS 'ecole', E.`cp` AS 'cpe', P.`pcnd`, P.`carte`,P.`empreinte`, P.`photo`, P.`valide` AS cni, P.`comment`,
			0 as dejapaye
			FROM `professeur` P
			INNER JOIN `ecole`E
				ON E.`id` = P.`id_ecole`
			AND `empreinte` IN (
				SELECT P.`empreinte` FROM `professeur` P
				WHERE  P.`cnd`=1
				GROUP BY `empreinte`
				having count(P.`empreinte`) =1
				) 
			AND P.`cnd`=1
			) 
		) T
		left join `fichiers` F1 
			on F1.`prof_id`=T.`id`
			and F1.`typedoc`=1
		left join `fichiers` F2
			on F2.`prof_id`=T.`id`
			and F2.`typedoc`=2
;";


		$q0 = $this->_db->prepare($sql) ;
		$q0->execute() ;
		$tuples = $q0->fetchAll(PDO::FETCH_ASSOC) ;

		return $this->replaceKeys($tuples, self::NEWKEYS);
	}
	
	/**
	 * duplicates (profs)
	 *
	 * @return array
	 */
	public function apiDuplicates() : array {
		$sql = 	"SELECT P1.`id`,P1.`nom`, P1.`prenom`, T3.`nom` AS 'ecole', P1.`empreinte`
		FROM  `professeur` P1
       		INNER JOIN `ecole` T3
       		ON P1.`id_ecole`= T3.`id`
		WHERE  EXISTS (SELECT *
		               FROM   `professeur` P2
		               WHERE  P1.`id` <> P2.`id`
		               AND  P1.`empreinte` = P2.`empreinte`
		              AND P1.`cnd`=1
					  AND P2.`cnd`=1)
    	ORDER BY P1.`empreinte`, P1.`id` ;";

		$q0 = $this->_db->prepare($sql) ;
		$q0->execute() ;
		$tuples = $q0->fetchAll(PDO::FETCH_ASSOC) ;

		return $this->replaceKeys($tuples, self::NEWKEYS);

	}
	
	/**
	 * apiTeacherStringPut
	 *
	 * @param  int $id
	 * @param  string $leType
	 * @param  string $value
	 * @return int
	 */
	public function apiTeacherStringPut(int $id, String $leType, String $value) : int {
		$sql = "UPDATE `professeur` SET $leType=:VALEUR WHERE id=:ID" ;
		$stmt =  $this->_db->prepare($sql) ;
		$stmt->bindValue(':ID', $id ,PDO::PARAM_INT);
		$stmt->bindValue(':VALEUR', $value ,PDO::PARAM_STR);
		$bStatus = $stmt->execute();
		$count = $stmt->rowCount();

		return 0;
	}
	
	/**
	 * apiTeacherIntegerPut
	 *
	 * @param  int $id
	 * @param  string $leType
	 * @param  int $value
	 * @return int
	 */
	public function apiTeacherIntegerPut(int $id, String $leType, int $value) : int {
		return 0;
	}	
		
	/**
	 * apiTeacherPut
	 * return -1 (error), 0 (not modified), >=1 (nb lines modified)
	 *
	 * @param  mixed $id
	 * @param  mixed $leType
	 * @param  mixed $value
	 * @param  mixed $typeOf
	 * @return int
	 */
	public function apiTeacherPut(int $id, string $leType, $value, string $typeOf) : int {

		$sql = "UPDATE `professeur` SET $leType=:VALEUR WHERE id=:ID" ;
		$stmt =  $this->_db->prepare($sql) ;
		$stmt->bindValue(':ID', $id ,PDO::PARAM_INT);
		if ($typeOf==='int') {
			$stmt->bindValue(':VALEUR', $value ,PDO::PARAM_INT);
		} else {
			$stmt->bindValue(':VALEUR', $value ,PDO::PARAM_STR);
		}
		$bStatus = $stmt->execute(); // revoie true ou false

		if ($bStatus) {
			$count = $stmt->rowCount();
		} else {
			$count = -1;
		}
		return $count;
	}	

	/**
	 * @param array $input
	 * @param array $tr replacement array
	 * @return array
	 */
	private function replaceKeys(array $input, $tr) {
	    $myArray=array();
	    foreach ($input as $subArray) {
	        $myArray[] = array_combine(preg_replace(array_map(function($s){return "/^$s$/";},
	        array_keys($tr)),$tr, array_keys($subArray)), $subArray);
	    }
	    return $myArray;
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