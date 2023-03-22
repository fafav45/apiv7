<?php
/*
* METHODES
*
*/
namespace App\Manager;

use PDO;
use Exception;
use App\Repository\Connexion;

class EntryManager
{
	private $_db ;
	protected $_domain;
	
	const NEWKEYS=array('ecole_id' => 'school_id', 
	'categorie_id' => 'category_id', 
	'classe_id'=>'class_id', 
	'classe'=>'divisionName', 
	'pistecd_id'=>'track', 
	'duree'=>'duration', 
	'id_candidat'=>'candidate_id', 
	'titre'=>'title',
	'morceau'=>'song', 
	'prof_id'=>'teacher_id',
	'CD'=>'receivedMP3', 
	'mp3'=>'validatedMP3', 
	'auteur'=>'author', 
	'national'=>'isNational',
	'editdate'=>'editDate', 
	'nomecole'=>'schoolName',
	'nomprof'=>'teacherLastName',
	'valide_mp3'=>'validatedMP3',
	'nomstyle'=>'styleName',
	'style'=>'styleName',
	'nomcat'=>'categoryName',
	'categorie'=>'categoryName',
	'cat_name'=>'categoryName',
	'nom_mp3'=>'mp3Name',
	'id_mp3'=>'mp3_id',
	'date_mp3'=>'mp3Date',
	'genre'=>'gender',
	'natureid'=>'id');


	// constructeur

    public function __construct(Connexion $cnx)
    {
        $this->setDb($cnx->getBdd());
        $this->setDomain($cnx->getSubDomain());
    }

	
	// delete
	
	/**
	 * delete (passage+cand_group-fichiers)
	 *
	 * @param  int $id
	 * @return int
	 */
	function apiDelete( int $id ) : int {

		try {
			$this->_db->beginTransaction();
			
			$sql_passage = "DELETE from `passage` WHERE id= :ID";

			$passage_stmt =  $this->_db->prepare($sql_passage) ;
			$passage_stmt->bindValue(':ID', $id ,PDO::PARAM_INT);

			$ret = $passage_stmt->execute() ;

			$nbDeletedGroupRows = $passage_stmt->rowCount();

			//table cand_grp
			
			$sql_cand_grp = "DELETE FROM `cand_grp` WHERE `passage_id`= :ID ";

			$cand_grp_stmt =  $this->_db->prepare($sql_cand_grp) ;
			$cand_grp_stmt->bindValue(':ID', $id ,PDO::PARAM_INT);

			$ret = $cand_grp_stmt->execute() ;

			$nbDeletedCandidateRows = $cand_grp_stmt->rowCount();

			// l'ID du passage est maintenant unique. Il n'y a donc pas besoin du type (typedoc)

			$ret = $this->_db->commit();
			// Cette fonction retourne true en cas de succès ou false si une erreur survient.

			$statut = 1 ; // si on est là, c'est que tout s'est bien passé, même si aucune ligne n'a été supprimée !

		} catch (Exception $e) {
			$this->_db->rollBack();
			$statut = 0 ;
		}
		return($statut) ;
	}

	function apiEntryDelete( int $id, int $nature_id ) : int {

		// table à partir de nature_id
		$table = $this->tableFromNatureId($nature_id);
		if ($table === '')
			return -1;

		try {
			$this->_db->beginTransaction();
			
			//$sql_passage = "DELETE from `passage` WHERE id= :ID";
			$sql_passage = "DELETE from `$table` WHERE id= :ID";

			$passage_stmt =  $this->_db->prepare($sql_passage) ;
			$passage_stmt->bindValue(':ID', $id ,PDO::PARAM_INT);

			$ret = $passage_stmt->execute() ; // true or false
			$nbDeletedGroupRows = $passage_stmt->rowCount(); // returns the number of rows affected by a DELETE, INSERT, or UPDATE statement.

		
			//table cand_grp, seulement si duet or group
			
			if ($nature_id > 1) {
				$sql_cand_grp = "DELETE FROM `cand_grp` WHERE `id_groupe`=:ID AND type_groupe=:NATURE ";

				$cand_grp_stmt =  $this->_db->prepare($sql_cand_grp) ;
				$cand_grp_stmt->bindValue(':ID', $id ,\PDO::PARAM_INT);
				$cand_grp_stmt->bindValue(':NATURE', ($nature_id-1) ,\PDO::PARAM_INT);

				$ret = $cand_grp_stmt->execute() ;
				$nbDeletedCandidateRows = $cand_grp_stmt->rowCount();

			}

			$ret = $this->_db->commit();
			// Cette fonction retourne true en cas de succès ou false si une erreur survient.

			
			$statut = 1 ; // si on est là, c'est que tout s'est bien passé, même si aucune ligne n'a été supprimée !

		} catch (Exception $e) {
			$this->_db->rollBack();
			$statut = 0 ;
		}
		return($statut) ;
	}
	

	
	/**
	 * apiGet
	 *
	 * @param  int $id
	 * @return array
	 */
	public function apiGet(int $id, int $nature_id) : array
	{
	    $myArray = array();

			$sql_one="SELECT 
			case PA.`nature_id`
				WHEN 1 THEN 'individual'
				WHEN 2 THEN 'duet'
				WHEN 3 THEN 'group'
			END AS 'entryType',
			PA.*, 
			S.`actif` as 'activeStyle', 
			S.`nomstyle` as 'styleName', 
			CL.`nomclasse` as 'divisionName', 
			CA.`cat_name` as 'categoryName', 
			P.`nom` as 'teacherLastName', 
			P.`prenom` as 'teacherFirstName', 
			E.`nom` as 'schoolName',
			COUNT(CG.candidat_id) as 'nbcandidates' 
			FROM `v_entries` PA
			INNER JOIN `style` S
				ON PA.`style_id` = S.`idstyle`
			INNER JOIN `classe` CL
				ON PA.`classe_id` = CL.`idclasse`
			INNER JOIN `category` CA
				ON PA.`categorie_id` = CA.`cat_id` 
				AND PA.`classe_id` = CA.`classe_id` 
				AND PA.`style_id` = CA.`style_id` 
				AND CA.`nature_id`=PA.`nature_id`
			LEFT OUTER JOIN `professeur` P
				ON PA.`prof_id` = P.`id`
			LEFT OUTER JOIN `ecole` E
				ON PA.`ecole_id` = E.`id`
			INNER JOIN `v_cand_grp` CG
				ON PA.`id` = CG.`groupe_id` AND PA.`nature_id`=CG.`nature_id`
			WHERE 1=1
			AND PA.`id` = $id
			AND PA.`nature_id` = $nature_id
			GROUP BY PA.`id` ;";

	        $one_stmt = $this->_db->prepare($sql_one);
	        $one_stmt->execute();
	        $myArray= $one_stmt->fetchAll(PDO::FETCH_ASSOC) ;
	        $myArrayTranslated = $this->replaceKeys($myArray, self::NEWKEYS);
	        unset($myArray);unset($one_stmt);unset($sql_one);
	        return $myArrayTranslated;
	    
	}

	
	/**
	 * apiGetList
	 *
	 * @param  bool $musicValidated
	 * @param  bool $countOnly
	 * @param  bool $sumOnly
	 * @param  int $nature_id
	 * @param  int $teacher_id
	 * @param  int $student_id
	 * @return array
	 */
	public function apiGetList(bool $musicValidated, bool $countOnly=false, bool $sumOnly=false, int $nature_id=0, int $teacher_id=0, int $student_id=0) : ?array
	{

	    if($countOnly && !$sumOnly) {
	        $sql = "SELECT 
			case `nature_id`
				when 1 then 'individual'
				when 2 then 'duet'
				when 3 then 'group'
			end as 'entryType',
			count(*) as 'count'
			from `v_entries`
			GROUP BY `nature_id`; -- " ;
	        
	        $q = $this->_db->prepare($sql);
	        $q->execute();
	        $myArray= $q->fetchAll(PDO::FETCH_ASSOC) ;
	        
	        $myArrayTranslated = $myArray;
	        
	    } else if(!$countOnly && $sumOnly) {
	        $myArray = array();
	        $sql = "SELECT 'registered' as 'label', COUNT(*) as 'count' from `v_entries`; -- " ;
	        $q = $this->_db->prepare($sql);
	        $q->execute();
	        $myArray= $q->fetchAll(PDO::FETCH_ASSOC) ;
	        $myArrayTranslated = $myArray;
	    } else {
	        $myArray = array();

			$sql_clause = "";
			$sql_music_clause = "";
			$sql_teacher = "";
			$sql_student = "";

			switch ($nature_id) {
				case 1:
				case 2:
				case 3:
					$sql_clause = "AND PA.`nature_id`=$nature_id";
					break;
				default :
				$sql_clause = "";
			}

			if ($musicValidated) {
				$sql_music_clause = "AND PA.`mp3`=1";
			}
			if ($teacher_id !== 0) $sql_teacher = "AND PA.`prof_id`=$teacher_id";
			if ($student_id !== 0) $sql_student = "AND CG.`candidat_id`=$student_id";

			// requete globale :

			$sql="SELECT
			CASE PA.`nature_id`
				when 2 then 'duet'
				when 1 then 'individual'
				when 3 then 'group'
			END AS 'entryType',
			PA.*, 
			S.`actif` as 'activeStyle', 
			S.`nomstyle` as 'styleName', 
			CL.`nomclasse` as 'divisionName', 
			CA.`cat_name` as 'categoryName', 
			P.`nom` as 'teacherLastName', P.`prenom` as 'teacherFirstName', 
			E.`nom` as 'schoolName', 
			count(DISTINCT CG.`candidat_id`) as 'nbcandidates' 
			FROM `v_entries` PA
			INNER JOIN  `style` S
				ON PA.`style_id` = S.`idstyle`
			INNER JOIN `classe` CL
				ON PA.`classe_id` = CL.`idclasse`
			INNER JOIN `category` CA
				ON PA.`categorie_id` = CA.`cat_id`
				AND PA.`classe_id`=CA.`classe_id`
				AND PA.`style_id`=CA.`style_id` 
				AND CA.`nature_id`=PA.nature_id
			LEFT OUTER JOIN `professeur` P
				ON PA.`prof_id` = P.`id`
			LEFT OUTER JOIN `ecole` E
				ON PA.`ecole_id` = E.`id`           
			LEFT OUTER JOIN v_cand_grp CG
				ON PA.`id` = CG.`groupe_id` AND PA.`nature_id`=CG.`nature_id`  
			WHERE 1=1
			$sql_clause
			$sql_music_clause
			$sql_teacher
			$sql_student
			group by PA.`id`, PA.`nature_id`
			ORDER BY PA.`titre`;";

			$stmt = $this->_db->prepare($sql);
			$stmt->execute();
			$myArray1= $stmt->fetchAll(PDO::FETCH_ASSOC) ;
			
	        $myArrayTranslated = $this->replaceKeys($myArray1, self::NEWKEYS);
			unset($myArray1);unset($stmt);unset($sql);
	    }
	    return $myArrayTranslated;
	}

     /**
	 * @param  string setDisplayLang
	 * @param  string datecalc
	 * @param string region
	 * @return PDO
	 * apiCsv
	 * lang, datedebut, region
	 */

	public function apiCsv($setDisplayLang, string $datecalc='2023-01-01', string $region="CND") {

		$lib_individual = "Individuel";
		$lib_candidate = "Candidat";
		$lib_duet = "Duo";
		$lib_group = "Groupe";
		$lib_free = "Candidat libre";

		if ($setDisplayLang === 'en_IL' OR $setDisplayLang ==='en_EN') {
			$lib_individual = "Individual";
			$lib_duet = "Duet";
			$lib_group = "Group";
			$lib_free = "External candidate";
			$lib_candidate = "Candidate";
		}

		$sql_select="SELECT
		CASE `PA`.`nature_id` 
			when 1 then CONCAT('IND',PA.`id`)
			when 2 then CONCAT('DUO',PA.`id`)
			when 3 then CONCAT('GRP',PA.`id`)
			else null
		END AS 'IDCND',
		'$region' AS Region,
		CASE PA.`nature_id`
			when 1 then '$lib_individual' -- remplacement
			when 2 then '$lib_duet' 
			when 3 then '$lib_group'
		END AS 'Nature',
		CASE PA.`nature_id`
			when 1 then C.`nom`
			when 2 then GROUP_CONCAT(C.`prenom`,' ',C.`nom` SEPARATOR ' & ')
			when 3 then ''
		END AS 'Nom',
		CASE PA.`nature_id`
			when 1 then C.`prenom`
			when 2 then GROUP_CONCAT(C.`prenom` SEPARATOR ' & ')
			when 3 then ''
		END AS 'Prenom',
        -- Sexe pour groupe et duo
		CASE PA.`nature_id`
			when 1 then C.`sexe`
			when 2 then ''
			when 3 then ''
		END AS 'Sexe',
		-- a remplacer
		CASE PA.`pistecd_id` 
			WHEN 0 THEN PA.`titre` ELSE CONCAT('Imposé N°',PA.`pistecd_id`) END AS 'Titre',
		CA.`cat_name` AS 'Categorie',
        CL.`nomclasse` as 'Classe',
		S.`nomstyle` as 'Style',	
        E.`nom` as 'Ecole',
		E.`id` as 'Ecole_ID',	
		CASE PA.`prof_id` when 9999 then '$lib_free' else CONCAT(P.`prenom`,' ',P.`nom`) END as Professeur,
		CASE PA.`prof_id`
			when 9999 then C.`telephone1` 
			else P.`telephone`
		END as 'Telephone',
		CASE PA.`prof_id`
			when 9999 then C.`email` 
			else P.`email`
		END as 'Mail',        
		CASE PA.`pistecd_id` WHEN 0 THEN '' ELSE PA.`pistecd_id` END AS 'Piste',
		PA.`duree` AS 'Duree',
		PA.`placement` AS 'Placement',
		CASE PA.`pistecd_id` WHEN 0 THEN PA.`morceau` ELSE IMP.`titre` END AS 'Morceau',
		CASE PA.`pistecd_id` WHEN 0 THEN PA.`auteur` ELSE IMP.`auteur` END AS  'Auteur',
			FLOOR(AVG(TIMESTAMPDIFF(MONTH, C.`date_naissance`, '$datecalc'))/12) 'Age_Ans', 
			FLOOR(AVG(TIMESTAMPDIFF(MONTH, C.`date_naissance`, '$datecalc'))%12) 'Age_Mois',
		CASE PA.`CD` WHEN 0 THEN '' ELSE PA.`CD` END AS 'CD_Recu', 
		'' AS 'Remarque',
		'' AS 'Num_Passage',
		'' AS 'Heure_Passage',
		'' AS 'Heure_Convoc',
		'' AS 'Session',
        '' As 'Jour',
        count(*) as 'Nombre',
		group_concat(CONCAT(C.prenom, ' ', C.nom, ' (', DATE_FORMAT(C.`date_naissance`, '%d/%m/%Y'),')') SEPARATOR ';') as '$lib_candidate 1'
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
		-- modif 2023-02-20, ajout de PA.nature_id dans regroupement
		-- il peut y avoir des Id identiques avec nature_id differents
		GROUP BY `PA`.`id`, `PA`.`nature_id`
        ORDER BY PA.`nature_id`, C.`nom`, C.`prenom`;";

		$select_stmt = $this->_db->prepare($sql_select) ;
		$select_stmt->execute() ;

		return($select_stmt);
	}
	
		
	/**
	 * setDb
	 *
	 * @param  PDO $db
	 * @return void
	 */
	public function setDb(PDO $db)
	{
		$this->_db = $db;
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
	 * apiGetMusicListPerNatureId
	 *
	 * @param  mixed $nature_id
	 * @return array
	 */
	public function apiGetMusicListPerNatureId(int $nature_id) : array {

		$sql_where_type="PA.`nature_id`=$nature_id";
		// code inutile, la nature est obligatoire
		if ($nature_id === null || $nature_id === 0) {
			$sql_where_type="1=1";
		}
		$typedoc = $nature_id + 4;

		$sqm_mp3="SELECT PA.`id` AS 'natureid',
		PA.`titre`,
		PA.`morceau`,
		PA.`duree` as 'duree', 
		E.`nom` as 'nomecole',
		P.`nom` as 'nomprof',
		PA.`mp3` as 'valide_mp3',
		S.`nomstyle`,
		CAT.`cat_name`,
		PA.`comment`,
		F1.`nom` as 'nom_mp3',
		F1.`id` as 'id_mp3',
		F1.`date` as 'date_mp3'
		FROM `v_entries` PA
		LEFT OUTER JOIN `professeur` P
			ON PA.`prof_id` = P.`id`
		LEFT OUTER JOIN `ecole` E
			ON PA.`ecole_id` = E.`id` 
		LEFT OUTER JOIN `style` S
			ON PA.`style_id` = S.`idstyle`
		INNER JOIN `category` CAT
			ON PA.`categorie_id` = CAT.`cat_id`
			AND PA.`classe_id`=CAT.`classe_id`
			AND PA.`style_id`=CAT.`style_id` 
			AND CAT.`nature_id`=PA.`nature_id`
		LEFT OUTER JOIN `fichiers` F1 
			ON F1.`passage_id`=PA.`id` AND F1.`typedoc`=$typedoc
		LEFT OUTER JOIN `fichiers` F2
			ON F2.`passage_id`=PA.`id` AND F2.`typedoc`=$typedoc
		WHERE ($sql_where_type AND (PA.`pistecd_id`=0 OR PA.`pistecd_id` IS NULL))
		ORDER BY PA.`titre`";

		$mp3_stmt = $this->_db->prepare($sqm_mp3);
		$mp3_stmt->execute();
		$tuples= $mp3_stmt->fetchAll(PDO::FETCH_ASSOC) ;
		$myArrayTranslated = $this->replaceKeys($tuples, self::NEWKEYS);

		return $myArrayTranslated;
	}

	
	/**
	 * followUp
	 *
	 * @return array
	 */
	public function apiFollowUp() : array{

		$sql="SELECT 
		CASE PA.`nature_id`
			when 2 then 'duo'
			when 1 then 'ind'
			when 3 then 'grp'
		END nature,
		PA.`id` as 'id',
		PA.`duree` as 'duration', 
		S.`nomstyle` as 'styleName', 
		CL.`nomclasse` as 'divisionName', 
		CA.`cat_name` as 'categoryName',
		CASE PA.`nature_id`
			when 1 then C.`sexe`
			else ''
		END gender
		FROM `v_entries` PA
		INNER JOIN  `style` S
			ON PA.`style_id` = S.`idstyle`
		INNER JOIN `classe` CL
			ON PA.`classe_id` = CL.`idclasse`
		INNER JOIN `category` CA
			ON PA.`categorie_id` = CA.`cat_id`
			AND PA.`classe_id`=CA.`classe_id`
			AND PA.`style_id`=CA.`style_id` 
			AND CA.`nature_id`=PA.`nature_id`
		LEFT OUTER JOIN v_cand_grp CG
			ON PA.`id` = CG.`groupe_id` AND PA.`nature_id`=CG.`nature_id`
		INNER JOIN `candidat` C 
			ON CG.`candidat_id` = C.`id`
		GROUP BY PA.`id`, PA.`nature_id`
		ORDER BY PA.`nature_id`, S.`nomstyle`,CA.`cat_name`,CL.`nomclasse`;";

		$q0 = $this->_db->prepare($sql);
		$q0->execute();
		$tuples= $q0->fetchAll(PDO::FETCH_ASSOC) ;

		$myArrayTranslated = $this->replaceKeys($tuples, self::NEWKEYS);
		return $myArrayTranslated;
	}
	
	/**
	 * apiGetListForJava
	 *
	 * @param  int $nature_id
	 * @param  bool $national
	 * @return array
	 */
	public function apiGetListForJava(int $nature_id=0, bool $national=false) : array {

		if ($national === true) {
			$sql_where_national = " AND PA.`national`=1";
		} else {
			$sql_where_national = '';
		}

		if ($nature_id !== 0) {
			$sql_where_type = "AND PA.`nature_id`=$nature_id";
			$sql_where_type .= " AND F.typedoc=";
			$sql_where_type .= ( $nature_id + 4 );
		} else {
			$sql_where_type="";
		}

		// si on prend tous les passages, il y a conflit d'ID
		// intégrer dans la recherche le typedoc (5:ind, 6:duo, 7:groupe)
		// typedoc = nature_id + 4

		$sql_java = "SELECT PA.`id` as 'natureid', 
		CASE PA.`nature_id`
			WHEN 1 THEN 'ind'
			WHEN 2 THEN 'duo'
			WHEN 3 THEN 'grp'
		END AS nature,
		F.id as fmp3id, 
		F.nom as fmp3, 
		S.`nomstyle` as 'style', 
		PA.`titre`
		FROM `v_entries` PA
		LEFT JOIN  `style` S
			ON PA.`style_id` = S.`idstyle`
		INNER JOIN fichiers F 
			ON F.passage_id=PA.id -- AND F.typedoc=7
		WHERE 1=1
		$sql_where_type
		AND PA.`pistecd_id`=0
		$sql_where_national ;" ;

		$java_stmt = $this->_db->prepare($sql_java);
		$java_stmt->execute();
		$tuples= $java_stmt->fetchAll(PDO::FETCH_ASSOC) ;

		$myArrayTranslated = $this->replaceKeys($tuples, self::NEWKEYS);
		return $myArrayTranslated;
	}
	
	

		
	/**
	 * tableFromNatureId
	 *
	 * @param  int $nature_id
	 * @return string
	 */
	public function tableFromNatureId(int $nature_id) : string {
		switch($nature_id) {
			case 1:
				$table = 'individuel';
				break;
			case 2:
				$table = 'duo';
				break;
			case 3:
				$table = 'groupe';
				break;
			default:
				$table = '';
		}
		return $table;
	}

	public function updateMP3Comment(int $id, int $nature_id, string $value) : int {
	
		$table = $this->tableFromNatureId($nature_id);
		if ($table === '')
			return false;

		$sql_update = "UPDATE `$table` SET `comment`=:VALEUR WHERE id=:MONID" ;
		$update_stmt = $this->_db->prepare($sql_update);
		$update_stmt->bindValue(':MONID', $id ,PDO::PARAM_INT);
		$update_stmt->bindValue(':VALEUR', $value ,PDO::PARAM_STR);
		$statut = $update_stmt->execute() ;

		$count = ($statut === true)?$update_stmt->rowCount():-1;
		return $count;
	}

	public function updateMP3Validation(int $id, int $nature_id, int $value) : int {
		
		$table = $this->tableFromNatureId($nature_id);
		if ($table === '')
			return false;

		$sql_update = "UPDATE `$table` SET `mp3`=:VALEUR WHERE id=:MONID" ;
		$update_stmt = $this->_db->prepare($sql_update);
		$update_stmt->bindValue(':MONID', $id ,\PDO::PARAM_INT);
		$update_stmt->bindValue(':VALEUR', $value ,\PDO::PARAM_INT);
		$statut = $update_stmt->execute() ;

		$count = ($statut === true)?$update_stmt->rowCount():-1;
		return $count;
	}

	public function setDomain(string $arg): void
    {
        $this->_domain = $arg;
    }

}