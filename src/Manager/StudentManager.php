<?php

namespace App\Manager;

use PDO;
use App\Repository\Connexion;

class StudentManager
{
	private $_db ;
	protected $_domain;
	
	const NEWKEYS=array('ecole_id' => 'school_id', 'categorie_id' => 'category_id', 'classe_id'=>'class_id', 'pistecd_id'=>'track', 'duree'=>'duration', 'id_candidat'=>'candidate_id', 'titre'=>'title','morceau'=>'song','prof_id'=>'teacher_id','CD'=>'receivedMP3','mp3'=>'validatedMP3','auteur'=>'author',
	    'nom'=>'lastName','prenom'=>'firstName','date_naissance'=>'birthday','naissance'=>'birthday','adresse1'=>'address1','adresse2'=>'additionalAddress','ville'=>'city','cp'=>'postalCode','telephone1'=>'phoneNumber1','telephone2'=>'phoneNumber2','id_ecole'=>'school_id','ecole'=>'schoolName', 'sexe'=>'gender',
		'atmin'=>'minorCertificate','atmaj'=>'majorCertificate','valide'=>'validatedBirthday','photo'=>'validatedPhoto','cnd'=>'cndMember','adhbureau'=>'cndBoardMember','carte'=>'sentCard',
		'empreinte'=>'digitalPrint','ecole_cp'=>'schoolPostalCode','pcnd'=>'cndPaid','pind'=>'indPaid','pduo'=>'duoPaid', 'pgrp'=>'grpPaid','editdate'=>'editDate');

	
	// constructeur

	public function __construct(Connexion $cnx)
	{
        $this->setDb($cnx->getBdd());
        $this->setDomain($cnx->getSubDomain());
	}
	
	public function count()
	{
		// Exécute une requête COUNT() et retourne le nombre de résultats retourné.
		return $this->_db->query('SELECT COUNT(*) FROM `candidat`')->fetchColumn();
	}
			
	/**
	 * apiUpdate
	 *
	 * @param  int $id
	 * @param  string $column
	 * @param  mixed $valeur
	 * @param  string $typeOf
	 * @return int
	 */
	public function apiUpdate(int $id, string $column, $valeur, string $typeOf) : int {

		if (!is_numeric($id)) {
			trigger_error('StudentManager::apiUpdate : la valeur id reçue n\'est pas de type numeric', E_USER_WARNING);
			return -1;
		}

		$sql = "UPDATE `candidat` SET $column=:VALEUR WHERE id=:ID" ;
		$q = $this->_db->prepare($sql);

		$q->bindValue(':ID', $id ,PDO::PARAM_INT);
		if ($typeOf==='string') {
			$q->bindValue(':VALEUR', $valeur ,PDO::PARAM_STR);
		} else if ($typeOf==='int') {
			$q->bindValue(':VALEUR', $valeur ,PDO::PARAM_INT);
		}

		$status = $q->execute(); // Cette fonction retourne true en cas de succès ou false si une erreur survient.

		if ($status === true) {
			$count = $q->rowCount();
		} else {
			$count = -1;                              
		}

		return $count;
	}

	/**
	 * apiDelete
	 * 
	 * pas de suppression des fichiers !
	 * pas de controle des passages !
	 * 
	 * @param  int $id
	 * @return bool
	 */
	public function apiDelete(int $id)
	{
		$sql = "DELETE FROM `candidat` WHERE `id` = :ID";
		$q  =  $this->_db->prepare($sql) ;
		$q->bindValue(':ID', $id ,PDO::PARAM_INT);
		$statut = $q->execute() ;
		return($statut) ;
	}
	
	public function apiValidate($id) {
		if (!is_numeric($id)) {
			trigger_error('CandidatManager::validate : la valeur reçue n\'est pas de type numeric', E_USER_WARNING);
			return;
		}
		$sql = "UPDATE `candidat` SET `valide`=1 WHERE id=:MONID" ;
		$q = $this->_db->prepare($sql);
		$q->bindValue(':MONID', $id ,PDO::PARAM_INT);
		$statut = $q->execute() ;
		return (bool) $statut ;
	}
	
	/**
	 * validatePhoto
	 *
	 * @param  int $id
	 * @return bool
	 */
	public function apiValidatePhoto($id) : bool{
		if (!is_numeric($id)) {
			trigger_error('CandidatManager::validatePhoto : la valeur reçue n\'est pas de type numeric', E_USER_WARNING);
			return false;
		}
		$sql = "UPDATE `candidat` SET `photo`=1 WHERE id=:MONID" ;
		$q = $this->_db->prepare($sql);
		$q->bindValue(':MONID', $id ,PDO::PARAM_INT);
		$statut = $q->execute() ;
		return (bool) $statut ;
	}
	
	/**
	 * unValidate
	 *
	 * @param  int $id
	 * @return bool
	 */
	public function apiUnValidate($id) :bool{
		if (!is_numeric($id)) {
			trigger_error('CandidatManager::unValidate : la valeur reçue n\'est pas de type numeric', E_USER_WARNING);
			return false;
		}
		$sql = "UPDATE `candidat` SET `valide`=0 WHERE id=:MONID" ;
		$q = $this->_db->prepare($sql);
		$q->bindValue(':MONID', $id ,PDO::PARAM_INT);
		$statut = $q->execute() ;
		return (bool) $statut ;
	}
	
	/**
	 * unValidatePhoto
	 *
	 * @param  mixed $id
	 * @return bool
	 */
	public function apiUnValidatePhoto($id) :bool{
		if (!is_numeric($id)) {
			trigger_error('CandidatManager::unValidatePhoto : la valeur reçue n\'est pas de type numeric', E_USER_WARNING);
			return false;
		}
		$sql = "UPDATE `candidat` SET `photo`=0 WHERE id=:MONID" ;
		$q = $this->_db->prepare($sql);
		$q->bindValue(':MONID', $id ,PDO::PARAM_INT);
		$statut = $q->execute() ;
		return (bool) $statut ;
	}
	
	/**
	 * unValidateAtmin
	 *
	 * @param  mixed $id
	 * @return bool
	 */
	public function apiUnValidateAtmin($id) : bool{
		if (!is_numeric($id)) {
			trigger_error('CandidatManager::unValidateAtmin : la valeur reçue n\'est pas de type numeric', E_USER_WARNING);
			return false;
		}
		$sql = "UPDATE `candidat` SET `atmin`=0 WHERE id=:MONID" ;
		$q = $this->_db->prepare($sql);
		$q->bindValue(':MONID', $id ,PDO::PARAM_INT);
		$statut = $q->execute() ;
		return (bool) $statut ;
	}
	
	/**
	 * unValidateAtmaj
	 *
	 * @param  mixed $id
	 * @return bool
	 */
	public function apiUnValidateAtmaj($id) : bool{
		if (!is_numeric($id)) {
			trigger_error('CandidatManager::unValidateAtmaj : la valeur reçue n\'est pas de type numeric', E_USER_WARNING);
			return false;
		}
		$sql = "UPDATE `candidat` SET `atmaj`=0 WHERE id=:MONID" ;
		$q = $this->_db->prepare($sql);
		$q->bindValue(':MONID', $id ,PDO::PARAM_INT);
		$statut = $q->execute() ;
		return (bool) $statut ;
	}
		
	/**
	 * validateAll
	 *
	 * @return bool
	 */
	public function apiValidateAll() : bool {
		$sql = "UPDATE `candidat` SET `valide`=1 WHERE 1" ;
		$q = $this->_db->prepare($sql);
		$statut = $q->execute() ;
		return (bool) $statut ;
	}
	

	/**
	 * apiGet: return all properties from database for the selected student
	 *
	 * @param  int $id
	 * @param  string $opt_startDate
	 * @return array
	 */
	public function apiGet(int $id , string $opt_startDate = null) : array
	{
	    if ($opt_startDate == null)
	        $opt_startDate = date('Y-m-d');

		$sql = "SELECT T.*,
		COUNT(CASE `PA`.`nature_id` when 1 then 1 else null END) AS 'nb_individuals',
		COUNT(CASE `PA`.`nature_id` when 2 then 1 else null END) AS 'nb_duets',
		COUNT(CASE `PA`.`nature_id` when 3 then 1 else null END) AS 'nb_groups',
		COUNT(PA.`id`) AS 'nb_entries'
		FROM -- candidats deja filtrés + nb files
			(SELECT C.*, 
			TIMESTAMPDIFF(YEAR,C.`date_naissance`, :DATE) as 'age',
			E.`nom` AS 'schoolName', 
			COUNT(DISTINCT F.`id`) as 'nbFiles'
			FROM `candidat` C
			INNER JOIN `ecole` E
				ON C.`id_ecole` = E.`id`
			LEFT JOIN fichiers F
				ON F.`candidat_id` = C.`id`
			group by C.`id` -- une ligne par candidat	
			) T -- table tempraire des candidats, avec nbFiles
		LEFT JOIN `v_cand_grp` CG
			ON T.`id` = CG.`candidat_id`
		LEFT JOIN `v_entries` PA
			ON PA.`id` = CG.`groupe_id`
			AND PA.`nature_id`=CG.`nature_id`
		WHERE T.`id`=:IDENTIFIER
		GROUP BY T.id -- une ligne par candidat
		ORDER BY T.`nom`, T.`prenom`, T.`schoolName`";
        
        $q = $this->_db->prepare( $sql );
        $q->bindValue(':IDENTIFIER', $id, PDO::PARAM_INT);
        $q->bindValue(':DATE', $opt_startDate ,PDO::PARAM_STR);
        $q->execute();
        
        //$myArray= $q->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'API') ;
        $myArray= $q->fetchAll(PDO::FETCH_ASSOC) ;
        $myArrayTranslated = $this->replaceKeys($myArray, self::NEWKEYS);
        return( $myArrayTranslated );
	}
	
	/**
	 * return all properties from database for filetered candidates
	 */
		
	/**
	 * apiGetAll
	 *
	 * @param  bool $opt_startDate
	 * @param  bool $opt_birthdayValidated
	 * @param  bool $opt_photoValidated
	 * @param  bool $member
	 * @param  bool $boardMember
	 * @param  bool $countOnly
	 * @param  bool $usedOnly
	 * @return array
	 */
	public function apiGetAll(string $opt_startDate=null, bool $opt_birthdayValidated=false, bool $opt_photoValidated=false, bool $member=false, bool $boardMember=false, bool $usedOnly=false) : array
	{	
	    
	    $myArray   = array();
	    $where     = 'WHERE 1=1';
		$having    = '';
	    
	    if($opt_birthdayValidated)
	          $where .= ' AND C.`valide`=1';
	              
	    if($opt_photoValidated)
	          $where .= ' AND C.`photo`=1';

	    if($member)
              $where .= ' AND C.`cnd`=1';
	  
        if($boardMember)
          $where .= ' AND C.`adhbureau`=1';
	    
	    if ($opt_startDate == null)
	        $opt_startDate = date('Y-m-d');

		if($usedOnly)
			$having .= 'HAVING `nb_entries` > 0';

		$sql = "SELECT T.*";

		$sql = "SELECT T.*, 
		COUNT(CASE `PA`.`nature_id` when 1 then 1 else null END) AS 'nb_individuals',
		COUNT(CASE `PA`.`nature_id` when 2 then 1 else null END) AS 'nb_duets',
		COUNT(CASE `PA`.`nature_id` when 3 then 1 else null END) AS 'nb_groups',
		COUNT(PA.`id`) AS 'nb_entries'
		FROM 
			(SELECT C.*, 
			TIMESTAMPDIFF(YEAR,C.`date_naissance`, :DATE) as 'age',
			E.`nom` AS 'schoolName', 
			COUNT(DISTINCT F.`id`) as 'nbFiles'
			FROM `candidat` C
			INNER JOIN `ecole` E
				ON C.`id_ecole` = E.`id`
			LEFT JOIN `fichiers` F
				ON F.`candidat_id` = C.`id`
			$where
			group by C.`id` 
			) T 
		LEFT JOIN `v_cand_grp` CG
			ON T.`id` = CG.`candidat_id`
		LEFT JOIN `v_entries` PA
			ON PA.`id` = CG.`groupe_id`
			AND PA.`nature_id`=CG.`nature_id`
		GROUP BY T.id 
		$having
		ORDER BY T.`nom`, T.`prenom`, T.`schoolName`;";

	    $q = $this->_db->prepare( $sql );
	    $q->bindValue(':DATE', $opt_startDate ,PDO::PARAM_STR);
	    $q->execute();
	  
		$myArray= $q->fetchAll(PDO::FETCH_ASSOC) ;

		// if ($countOnly) {
		// 	return array("count" => count($myArray));
		// } else {
		// 	$myArrayTranslated = $this->replaceKeys($myArray, SELF::NEWKEYS);
	    // 	return( $myArrayTranslated );
		// }


			$myArrayTranslated = $this->replaceKeys($myArray, SELF::NEWKEYS);
	    	return( $myArrayTranslated );

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
		
	
	/**
	 * duplicates
	 *
	 * @return array
	 */
	public function apiDuplicates() : array {
		$sql = 	'SELECT T1.`id`,T1.`nom`, T1.`prenom`, T3.`nom` AS ecole
		FROM  `candidat` T1
       		INNER JOIN `ecole` T3
       		ON T1.`id_ecole`= T3.`id`
		WHERE  EXISTS (SELECT *
		               FROM   `candidat` T2
		               WHERE  T1.`id` <> T2.`id`
		               AND  T1.`empreinte` = T2.`empreinte`
		               AND T1.`cnd`=1
					   AND T2.`cnd`=1)';

		$q0 = $this->_db->prepare($sql) ;
		$q0->execute() ;
		$myArray = $q0->fetchAll(PDO::FETCH_ASSOC) ;
		$myArrayTranslated = $this->replaceKeys($myArray, SELF::NEWKEYS);

	    return $myArrayTranslated;

	}
	
	/**
	 * followUp (adhesions)
	 *
	 * @return array
	 */
	public function apiFollowUp() : array {

		$sql_select="SELECT 
		C.`id`,
		C.`nom` AS 'nom',
		C.`prenom` AS 'prenom',
		C.`date_naissance`,
		C.`cp` as 'postalCode',
		C.`carte` AS 'sentCard',
		E.`nom` as 'schoolName',
		E.`cp` as 'schoolPostalCode',
		C.`pcnd` as 'cndPaid',
		CASE 
			WHEN T2.`nb`>1 AND LENGTH(C.`pcnd`)>1 THEN 0 
			WHEN T2.`nb`=1 then 0
			ELSE 1
		END as 'alreadyPaid',
		C.`pind` as 'indPaid',
		C.`pduo` as 'duoPaid',
		C.`pgrp` as 'grpPaid',
		C.`atmin` as minorCertificate,
		C.`atmaj` as majorCertificate,
		C.`valide` AS valid,
		C.`photo` as validatedPhoto,
		C.`empreinte` as 'digitalPrint',
		C.`comment`,
		F1.`id` as cni_id, F1.`nom` as cniName, 
		F2.`id` as photo_id, F2.`nom` as photoName,
		F3.`id` as minorCertificate_id, F3.`nom` as minorCertificateName,
		F4.`id` as majorCertificate_id, F4.`nom` as majorCertificateName,
		
		COUNT(CASE `PA`.`nature_id` when 1 then 1 else null END) AS 'nb_individuals',
		COUNT(CASE `PA`.`nature_id` when 2 then 1 else null END) AS 'nb_duets',
		COUNT(CASE `PA`.`nature_id` when 3 then 1 else null END) AS 'nb_groups'
		FROM `candidat` C
		INNER JOIN 
		(
			SELECT C.`id`, C.`empreinte`, T.`nb` as nb from `candidat` C 
			INNER JOIN(
			SELECT C.`empreinte`, COUNT(C.`empreinte`)	as nb FROM `candidat` C
			WHERE  C.`cnd`=1
			-- WHERE (C.`cnd`=1 OR C.`adhbureau`=1)
			GROUP BY C.`empreinte`
			) T
				ON T.`empreinte`=C.`empreinte`
		) T2
		ON T2.`id`=C.`id`
		INNER JOIN `ecole` E
			ON C.`id_ecole` = E.`id`
		LEFT JOIN `fichiers` F1
			ON F1.`candidat_id`=C.`id`
			AND F1.`typedoc`=1
		LEFT JOIN `fichiers` F2
			on F2.`candidat_id`=C.`id`
			AND F2.`typedoc`=2
		LEFT JOIN `fichiers` F3
			ON F3.`candidat_id`=C.`id`
			AND F3.`typedoc`=3
		LEFT JOIN `fichiers` F4
			ON F4.`candidat_id`=C.`id`
			AND F4.`typedoc`=4
		LEFT JOIN `v_cand_grp` CG 
			ON CG.`candidat_id`=C.`id`
		INNER JOIN `v_entries` PA 
			ON PA.`id` = CG.`groupe_id`
			AND PA.`nature_id`=CG.`nature_id`
		WHERE  C.`cnd`=1 -- effectif en 2022, à tester, y a des passages en trop, à cause du left join ?
		GROUP BY C.`id`
		ORDER BY C.`nom` ASC, C.`prenom` ASC,  CAST(C.`pcnd` AS SIGNED) DESC;";

		$select_stmt = $this->_db->prepare($sql_select) ;
		$select_stmt->execute() ;
		$tuples = $select_stmt->fetchAll(PDO::FETCH_ASSOC) ;
		$myArrayTranslated = $this->replaceKeys($tuples, self::NEWKEYS);

		return $myArrayTranslated;
	}
	
	/**
	 * apiAllFollowUp (adhesions + membres bureau)
	 *
	 * @return array
	 */
	public function apiAllFollowUp() : array {

		$sql_select="SELECT 
		C.`id`,
		C.`nom` AS 'nom',
		C.`prenom` AS 'prenom',
		C.`date_naissance`,
		C.`cp` as 'postalCode',
		C.`carte` AS 'sentCard',
		E.`nom` as 'schoolName',
		E.`cp` as 'schoolPostalCode',
		C.`pcnd` as 'cndPaid',
		CASE 
			WHEN T2.`nb`>1 AND LENGTH(C.`pcnd`)>1 THEN 0 
			WHEN T2.`nb`=1 then 0
			ELSE 1
		END as 'alreadyPaid',
		C.`pind` as 'indPaid',
		C.`pduo` as 'duoPaid',
		C.`pgrp` as 'grpPaid',
		C.`atmin` as 'minorCertificate',
		C.`atmaj` as 'majorCertificate',
		C.`valide` AS 'valid',
		C.`photo` as 'validatedPhoto',
		C.`empreinte` as 'digitalPrint',
		C.`comment`,
		F1.`id` as 'cni_id', F1.`nom` as cniName, 
		F2.`id` as 'photo_id', F2.`nom` as photoName,
		F3.`id` as 'minorCertificate_id', F3.`nom` as minorCertificateName,
		F4.`id` as 'majorCertificate_id', F4.`nom` as majorCertificateName,
		
		COUNT(CASE `PA`.`nature_id` when 1 then 1 else null END) AS 'nb_individuals',
		COUNT(CASE `PA`.`nature_id` when 2 then 1 else null END) AS 'nb_duets',
		COUNT(CASE `PA`.`nature_id` when 3 then 1 else null END) AS 'nb_groups'
		FROM `candidat` C
		INNER JOIN 
		(
			SELECT C.`id`, C.`empreinte`, T.`nb` as nb from `candidat` C 
			INNER JOIN(
			SELECT C.`empreinte`, COUNT(C.`empreinte`)	as nb FROM `candidat` C
			-- WHERE  C.`cnd`=1
			WHERE (C.`cnd`=1 OR C.`adhbureau`=1)
			GROUP BY C.`empreinte`
			) T
				ON T.`empreinte`=C.`empreinte`
		) T2
		ON T2.`id`=C.`id`
		INNER JOIN `ecole` E
			ON C.`id_ecole` = E.`id`
		LEFT JOIN `fichiers` F1
			ON F1.`candidat_id`=C.`id`
			AND F1.`typedoc`=1
		LEFT JOIN `fichiers` F2
			on F2.`candidat_id`=C.`id`
			AND F2.`typedoc`=2
		LEFT JOIN `fichiers` F3
			ON F3.`candidat_id`=C.`id`
			AND F3.`typedoc`=3
		LEFT JOIN `fichiers` F4
			ON F4.`candidat_id`=C.`id`
			AND F4.`typedoc`=4
		LEFT JOIN `v_cand_grp` CG 
			ON CG.`candidat_id`=C.`id`
		LEFT JOIN `v_entries` PA 
			ON PA.`id` = CG.`groupe_id`
			AND PA.`nature_id`=CG.`nature_id`
		WHERE (C.`cnd`=1 OR C.`adhbureau`=1) -- test, à priori bon car effectif en 2022
		GROUP BY C.`id`
		ORDER BY C.`nom` ASC, C.`prenom` ASC,  CAST(C.`pcnd` AS SIGNED) DESC;";

		$select_stmt = $this->_db->prepare($sql_select) ;
		$select_stmt->execute() ;
		$tuples = $select_stmt->fetchAll(PDO::FETCH_ASSOC) ;
		$myArrayTranslated = $this->replaceKeys($tuples, self::NEWKEYS);

		return $myArrayTranslated;	
	}
	
	/**
	 * apiBoardMemberFollowUp
	 *
	 * @return array
	 */
	public function apiBoardMemberFollowUp() : array {
		$sql_select="SELECT 
		C.`id`,
		C.`nom` AS nom,
		C.`prenom` AS prenom,
		C.`date_naissance`,
		C.`cp` as 'postalCode',
		C.`carte` AS 'sentCard',
		E.`nom` as 'schoolName',
		E.`cp` as 'schoolPostalCode',
		C.`pcnd` as 'cndPaid',
		CASE 
			WHEN T2.`nb`>1 AND LENGTH(C.`pcnd`)>1 THEN 0 
			WHEN T2.`nb`=1 then 0
			ELSE 1
		END as 'alreadyPaid',
		C.`valide` AS valid,
		C.`photo` as validatedPhoto,
		C.`empreinte` as 'digitalPrint',
		C.`comment`,
		F1.`id` as cni_id, F1.`nom` as cniName, 
		F2.`id` as photo_id, F2.`nom` as photoName,
		'0'  as 'nb_individuals',
		'0'  as 'nb_duets',
		'0'  as 'nb_groups'
		FROM `candidat` C
		INNER JOIN 
		(
			SELECT C.`id`, C.`empreinte`, T.`nb` as nb from `candidat` C 
			INNER JOIN(
			SELECT C.`empreinte`, COUNT(C.`empreinte`)	as nb FROM `candidat` C
			WHERE (C.`adhbureau`=1)
			GROUP BY C.`empreinte`
			) T
				ON T.`empreinte`=C.`empreinte`
		) T2
		ON T2.`id`=C.`id`
		INNER JOIN `ecole` E
			ON C.`id_ecole` = E.`id`
		LEFT JOIN `fichiers` F1
			ON F1.`candidat_id`=C.`id`
			AND F1.`typedoc`=1
		LEFT JOIN `fichiers` F2
			on F2.`candidat_id`=C.`id`
			AND F2.`typedoc`=2
		GROUP BY C.`id`
		ORDER BY C.`nom` ASC, C.`prenom` ASC,  CAST(C.`pcnd` AS SIGNED) DESC		
		";

		$select_stmt = $this->_db->prepare($sql_select) ;
		$select_stmt->execute() ;
		$tuples = $select_stmt->fetchAll(PDO::FETCH_ASSOC) ;
		$myArrayTranslated = $this->replaceKeys($tuples, self::NEWKEYS);

		return $myArrayTranslated;
	}

	/**
	 * apiCsv
	 *
	 */
	public function apiCsv($setDisplayLang, string $datecalc='2022-01-01') {

		$lib_individual = "Individuel";
		$lib_duet = "Duo";
		$lib_group = "Groupe";
		$lib_free = "Candidat libre";

		if ($setDisplayLang === 'en_IL' OR $setDisplayLang ==='en_EN') {
			$lib_individual = "Individual";
			$lib_duet = "Duet";
			$lib_group = "Group";
			$lib_free = "External candidate";
		}

		//$date=date_create_from_format("d/m/Y",$startDate);
		//$datecalc=date_format($date,"Y-m-d");

		// CASE PA.`prof_id` when 9999 then '$lib_free' else CONCAT(P.`prenom`,' ',P.`nom`) END as Professeur,

		$sql_select="SELECT 
		CASE `PA`.`nature_id` 
			when 1 then CONCAT('IND',PA.`id`)
			when 2 then CONCAT('DUO',PA.`id`)
			when 3 then CONCAT('GRP',PA.`id`)
			else null
		END AS 'IDCND',
		CASE `PA`.`nature_id` 
			when 1 then 'Individuel'
			when 2 then 'Duo'
			when 3 then 'Groupe'
			else null
		END AS 'Nature',
		C.`nom` AS 'nom',
		C.`prenom` AS 'prenom',
        DATE_FORMAT(C.`date_naissance`, '%d/%m/%Y') AS 'date_naissance',
		C.`sexe` AS 'Sexe',
		CASE PA.`pistecd_id` 
			WHEN 0 THEN PA.`titre` ELSE CONCAT('Imposé N°',PA.`pistecd_id`) END AS 'Titre',
        CA.`cat_name` AS 'Categorie',
        CL.`nomclasse` as 'Classe',
		S.`nomstyle` as 'Style',
        E.`nom` as 'Ecole',
		E.`id` as 'Ecole_ID',
		CASE PA.`prof_id` when 9999 then '$lib_free' else CONCAT(P.`prenom`,' ',P.`nom`) END as Professeur,
        CASE PA.`pistecd_id` 
			WHEN 0 THEN '' ELSE PA.`pistecd_id` END AS 'Piste',
        PA.`duree` as 'Duree',
        PA.`placement` AS 'Placement',
        CASE PA.`pistecd_id` WHEN 0 THEN PA.`morceau` ELSE IMP.titre END AS 'Morceau',
        CASE PA.`pistecd_id` WHEN 0 THEN PA.`auteur` ELSE IMP.auteur END AS  'Auteur',
		TIMESTAMPDIFF(YEAR, C.`date_naissance`, '$datecalc') AS 'Age_Ans',
		(TIMESTAMPDIFF(MONTH, C.`date_naissance`, '$datecalc')- TIMESTAMPDIFF(YEAR, C.`date_naissance`, '$datecalc') *12 ) AS 'Age_Mois',
		C.`valide` as 'cni',
		C.`photo` as 'photo',
        CASE PA.`pistecd_id` WHEN 0 THEN PA.`mp3` ELSE 1  END AS 'fichier_musique',
		C.`atmin`, 
		C.`atmaj` 
        FROM `candidat` C
		INNER JOIN `v_cand_grp` CG 
			ON CG.`candidat_id`=C.`id`
		INNER JOIN `v_entries` PA 
			ON PA.`id` = CG.`groupe_id`
			AND PA.`nature_id`=CG.`nature_id`
		INNER JOIN `ecole` E
			ON C.`id_ecole` = E.`id`
		INNER JOIN `category` CA
			ON PA.`categorie_id` = CA.`cat_id`
			AND PA.`classe_id`=CA.`classe_id`
			AND PA.`style_id`=CA.`style_id` 
			AND CA.`nature_id`=PA.nature_id
		INNER JOIN `classe` CL
			ON PA.`classe_id` = CL.`idclasse`
		INNER JOIN  `style` S
			ON PA.`style_id` = S.`idstyle`
		LEFT JOIN `professeur` P
			ON PA.`prof_id` = P.`id`
		LEFT JOIN `impose` IMP
			ON PA.pistecd_id = IMP.id
        WHERE (C.`cnd`=1) -- securité
		;";

		$select_stmt = $this->_db->prepare($sql_select) ;
		$select_stmt->execute() ;

		return $select_stmt;
	}

	public function setDomain(string $arg): void
    {
        $this->_domain = $arg;
    }
}