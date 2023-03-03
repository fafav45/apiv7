<?php
/*
* METHODES
* getList
*
1: // cni
2: // photo
3: // attestation mineur
4: // attestation majeur
5: // individuel
6: // duo
7: //  groupe
*/

namespace App\Manager;

use PDO;
use App\Repository\Connexion;

class FileManager
{
    private $_db ;
    private $_logger ;
    private $_domain = '';
    //protected $cnx;

    public const NEWKEYS=array('ecole_id' => 'school_id', 'candidat_id' => 'candidate_id', 'nom' => 'name', 'typedoc'=>'docType', 'passage_id'=>'entry_id', 'prof_id' => 'teacher_id');

    // constructeur

    public function __construct(Connexion $cnx)
    {
        $this->setDb($cnx->getBdd());
        $this->setDomain($cnx->getSubDomain());
    }

    /**
     * apiGetAll
     *
     * @param  bool $musicsCountOnly
     * @param  bool $certificatesCountOnly
     * @param  bool $IDsCountOnly
     * @param  bool $IDPhotosCountOnly
     * @param  bool $usedOnly
     * @param  bool $mp3Only
     * @return array
     */
    public function apiGetAll(bool $musicsCountOnly=false, bool $certificatesCountOnly=false, bool $IDsCountOnly=false, bool $IDPhotosCountOnly=false, bool $getUsedOnly=false, bool $mp3Only=false): array
    {
        $sql = "SELECT * FROM `fichiers`";
        $all = true;
        $myArray = array();
        // Class 'AmlTools' not found
        //$domaine = AmlTools::getSubDomain();
        $domaine = $this->_domain;

        /*
        * mp3Only : type=5, 6, 7
        * WHERE `typedoc` IN (5,6,7)
        */
        if ($mp3Only) {
            $sql .= " WHERE `typedoc` IN (5,6,7)";
        }

        /*
         * musics
         */

        if ($musicsCountOnly) {
            $all = false;

            $sql_mp3="SELECT 
			CASE WHEN (SUM(received)-sum(validated)) <0 THEN 0 ELSE SUM(received)-sum(validated) END AS 'toValidate', 
			SUM(validated) AS 'validated',
			CASE WHEN  (SUM(validated)+SUM(toValidate)-SUM(received)) < 0 THEN 0 ELSE (SUM(validated)+SUM(toValidate)-SUM(received)) END AS 'missing' FROM   
			(
			SELECT
			COUNT(*) AS 'toValidate', 0 AS 'validated', 0 AS 'received'
				FROM `v_entries` WHERE `pistecd_id`=0 AND `mp3`=0    
			UNION
			SELECT 
			0 as 'toValidate', count(*) as 'validated', 0 as 'received'
				from `v_entries` WHERE `pistecd_id`=0 AND `mp3`=1
			UNION
			SELECT 0 AS 'toValidate',  0 AS 'validated', count(*) AS 'received' 
				FROM `fichiers` WHERE `typedoc` IN (5,6,7) AND passage_id is not null
			) T1;" ;

            $mp3_stmt = $this->_db->prepare($sql_mp3);
            $mp3_stmt->execute();
            $myArrayTemp= $mp3_stmt->fetchAll(PDO::FETCH_ASSOC) ;

            array_push($myArray, array("label"=>"validated","count" => $myArrayTemp[0]['validated']));
            array_push($myArray, array("label"=>"toValidate","count" => $myArrayTemp[0]['toValidate']));
            array_push($myArray, array("label"=>"missing","count" => $myArrayTemp[0]['missing']));
        }

        /*
         * certificates
         */

        if ($certificatesCountOnly) {
            $all = false;

            $sql="SELECT 
			SUM(validated) AS 'validated', 
			SUM(toValidate) AS 'toValidate', 
			CASE WHEN SUM(validated)+SUM(toValidate)-SUM(received) <0 then 0 ELSE  SUM(validated)+SUM(toValidate)-SUM(received) END AS 'missing' 
			FROM (
			SELECT SUM(withoutValidation) AS 'toValidate', 0 AS 'validated', 0 AS 'received' FROM
			(SELECT count(*) AS 'withoutValidation' from `candidat` C
			WHERE C.`cnd`=1 AND C.`atmin`=0 AND C.`atmaj`=0) T1
			UNION ALL
			SELECT 0 AS 'toValidate', SUM(withValidation) AS 'validated', 0 AS 'received' FROM
			(select count(*) AS 'withValidation' from `candidat` C 
			WHERE C.`cnd`=1 AND (C.`atmin`=1 OR C.`atmaj`=1)) T2
			UNION ALL 
			SELECT 0 AS 'toValidate', 0 AS 'validated', SUM(received) AS 'received' FROM
			(select count(DISTINCT `candidat_id`) AS 'received' from `fichiers` F 
			WHERE F.`typedoc`=3 OR F.`typedoc`=4) T3
			) T4;
        ";

            $q = $this->_db->prepare($sql);
            $q->execute();
            $myArrayTemp= $q->fetchAll(PDO::FETCH_ASSOC) ;

            array_push($myArray, array("label"=>"validated","count" => $myArrayTemp[0]['validated']));
            array_push($myArray, array("label"=>"toValidate","count" => $myArrayTemp[0]['toValidate']));
            array_push($myArray, array("label"=>"missing","count" => $myArrayTemp[0]['missing']));
        }

        if ($IDsCountOnly) {
            $all = false;

            $sql="SELECT 
			SUM(validated) AS 'validated', 
			SUM(toValidate) AS 'toValidate', 
			CASE WHEN SUM(validated)+SUM(toValidate)-SUM(received) <0 then 0 ELSE  SUM(validated)+SUM(toValidate)-SUM(received) END AS 'missing' 
			FROM (
				SELECT SUM(withoutValidation) AS 'toValidate', 0 AS 'validated', 0 AS 'received' FROM
				(SELECT count(*) AS 'withoutValidation' from `candidat` C
				WHERE (C.`cnd`=1 OR C.`adhbureau`=1) AND C.`valide`=0) T1
			UNION ALL
				SELECT SUM(withoutValidation) AS 'toValidate', 0 AS 'validated', 0 AS 'received' FROM
				(SELECT count(*) AS 'withoutValidation' from `professeur` C
				WHERE C.`cnd`=1 AND C.`valide`=0) T5
			UNION ALL
				SELECT 0 AS 'toValidate', SUM(withValidation) AS 'validated', 0 AS 'received' FROM
				(select count(*) AS 'withValidation' from `candidat` C 
				WHERE (C.`cnd`=1 OR C.`adhbureau`=1) AND C.`valide`=1) T2
			UNION ALL 
				SELECT 0 AS 'toValidate', SUM(withValidation) AS 'validated', 0 AS 'received' FROM
				(select count(*) AS 'withValidation' from `professeur` C 
				WHERE C.`cnd`=1 AND C.`valide`=1) T6
			UNION ALL
				SELECT 0 AS 'toValidate', 0 AS 'validated', SUM(received) AS 'received' FROM
				(select count(DISTINCT `candidat_id`) AS 'received' from `fichiers` F
				INNER JOIN `candidat` C ON C.`id`=F.`candidat_id` AND (C.`cnd`=1 OR C.`adhbureau`=1)
				WHERE F.`typedoc`=1) T3
			UNION ALL
				SELECT 0 AS 'toValidate', 0 AS 'validated', SUM(received) AS 'received' FROM
				(select count(DISTINCT `prof_id`) AS 'received' from `fichiers` F
				INNER JOIN professeur C ON C.`id`=F.`prof_id` AND C.`cnd`=1
				WHERE F.`typedoc`=1 ) T7
			) T4;
            ";

            $q = $this->_db->prepare($sql);
            $q->execute();
            $myArrayTemp= $q->fetchAll(PDO::FETCH_ASSOC) ;

            //$myArray = array();
            array_push($myArray, array("label"=>"validated","count" => $myArrayTemp[0]['validated']));
            array_push($myArray, array("label"=>"toValidate","count" => $myArrayTemp[0]['toValidate']));
            array_push($myArray, array("label"=>"missing","count" => $myArrayTemp[0]['missing']));
            //return $myArray;
        }

        if ($IDPhotosCountOnly) {
            $all = false;

            $sql="SELECT 
			SUM(validated) AS 'validated', 
			SUM(toValidate) AS 'toValidate', 
			CASE WHEN SUM(validated)+SUM(toValidate)-SUM(received) <0 then 0 ELSE  SUM(validated)+SUM(toValidate)-SUM(received) END AS 'missing' 
			FROM ( -- cand without val
				SELECT SUM(withoutValidation) AS 'toValidate', 0 AS 'validated', 0 AS 'received' FROM
				(SELECT count(*) AS 'withoutValidation' from `candidat` C
				WHERE (C.`cnd`=1 OR C.`adhbureau`=1) AND C.`photo`=0) T1
			UNION ALL -- prof without val
				SELECT SUM(withoutValidation) AS 'toValidate', 0 AS 'validated', 0 AS 'received' FROM
				(SELECT count(*) AS 'withoutValidation' from `professeur` C
				WHERE C.`cnd`=1 AND C.`photo`=0) T5
			UNION ALL -- cand with val
				SELECT 0 AS 'toValidate', SUM(withValidation) AS 'validated', 0 AS 'received' FROM
				(select count(*) AS 'withValidation' from `candidat` C 
				WHERE (C.`cnd`=1 OR C.`adhbureau`=1) AND C.`photo`=1) T2
			UNION ALL  -- prof with val
				SELECT 0 AS 'toValidate', SUM(withValidation) AS 'validated', 0 AS 'received' FROM
				(select count(*) AS 'withValidation' from `professeur` C 
				WHERE C.`cnd`=1 AND C.`photo`=1) T6
			UNION ALL -- cand files
				SELECT 0 AS 'toValidate', 0 AS 'validated', SUM(received) AS 'received' FROM
				(select count(DISTINCT `candidat_id`) AS 'received' from `fichiers` F
				INNER JOIN `candidat` C ON C.`id`=F.`candidat_id` AND (C.`cnd`=1 OR C.`adhbureau`=1)
				WHERE F.`typedoc`=2) T3
			UNION ALL -- prof files
				SELECT 0 AS 'toValidate', 0 AS 'validated', SUM(received) AS 'received' FROM
				(select count(DISTINCT `prof_id`) AS 'received' from `fichiers` F
				INNER JOIN professeur C ON C.`id`=F.`prof_id` AND C.`cnd`=1
				WHERE F.`typedoc`=2 ) T7
			) T4;
            ";

            $q = $this->_db->prepare($sql);
            $q->execute();
            $myArrayTemp= $q->fetchAll(PDO::FETCH_ASSOC) ;

            //$myArray = array();
            array_push($myArray, array("label"=>"validated","count" => $myArrayTemp[0]['validated']));
            array_push($myArray, array("label"=>"toValidate","count" => $myArrayTemp[0]['toValidate']));
            array_push($myArray, array("label"=>"missing","count" => $myArrayTemp[0]['missing']));
            //return $myArray;
        }

        if ($all == true) {
            $q = $this->_db->prepare($sql);
            $q->execute();
            $myArray= $q->fetchAll(PDO::FETCH_ASSOC) ;
        }



        if ($all == true) {
            $myrootDir = $_SERVER['DOCUMENT_ROOT'];
            //$domaine='ins-2023';
            $fileDir = $myrootDir. DIRECTORY_SEPARATOR . "Files" . DIRECTORY_SEPARATOR . $domaine . DIRECTORY_SEPARATOR ;

            for ($i = 0; $i < count($myArray); ++$i) {
                $md5 = '';
                $file = $fileDir . $myArray[$i]['uniq_id'] ;
                if (file_exists($file)) {
                    $md5 = md5_file($file);
                }
                $myArray[$i]['md5']=$md5;
            }
        }

        $myArrayTranslated = $this->replaceKeys($myArray, self::NEWKEYS);

        return $myArrayTranslated;
    }

    /**
     * apiGet
     *
     * @param  int $id
     * @param  bool $withMD5
     * @param  string $domaine
     * @return array
     */
    public function apiGet(int $id, bool $withMD5=false): ?array
    {
        $sql = "SELECT * FROM fichiers where id=$id";

        $domaine=$this->_domain ;

        $q = $this->_db->prepare($sql);
        $q->execute();
        $myArray= $q->fetchAll(PDO::FETCH_ASSOC) ;
        $returnArray = array();

        // ne traiter que si un seul fichier ! sinon renvoyer tableau vide

        if ($myArray !== null && count($myArray) === 1) {
            $theItem = $myArray[0];
            //
            if ($withMD5) {
                $myrootDir = $_SERVER['DOCUMENT_ROOT'];

                $file = $myrootDir. DIRECTORY_SEPARATOR . "Files" . DIRECTORY_SEPARATOR . $domaine . DIRECTORY_SEPARATOR . $theItem['uniq_id'] ;
                if (file_exists($file)) {
                    $md5 = md5_file($file);
                } else {
                    $md5 = 'file does not exist...';
                }
                $theItem['md5'] = $md5;
            }
            array_push($returnArray, $theItem);
        } else {
            $theItem = null;
        }
        $myArrayTranslated = $this->replaceKeys($returnArray, self::NEWKEYS);
        return $myArrayTranslated;
    }

    /**
     * apiDeleteAllTeacher
     *
     * @param  int $arg
     * @param  string $domaine
     * @return bool
     */
    public function apiDeleteAllTeacher(int $arg, string $domaine=""): bool
    {
        /*
         * détruire les documents (CNI, photo) et les fichiers associés !
         * les fichiers physiques ne sont détruits que si les fichiers sont détruits en base.
         */

        //$arg ne peut être null ou égal à 0
        if (!is_numeric($arg) || $arg===0) {
            trigger_error('FileManager::apiDeleteAllTeacher : la valeur reçue n\'est pas de type numerique', E_USER_WARNING);
            return 0;
        }

        // step 1 : on détruit les fichiers physiques

        $sql = "SELECT `uniq_id` FROM `fichiers` WHERE `prof_id`=:PROFID";
        $q2 = $this->_db->prepare($sql);
        $q2->bindValue(':PROFID', $arg, PDO::PARAM_INT) ;
        $statut2 = $q2->execute() ;
        // on récupère le tableau
        $tabfic = $q2->fetchAll(PDO::FETCH_ASSOC) ;

        $myrootDir = $_SERVER['DOCUMENT_ROOT'];

        foreach ($tabfic as $t) {
            // détruire le fichier

            $file = join(DIRECTORY_SEPARATOR, array($myrootDir, 'Files', $domaine, $t['uniq_id']));
            if (file_exists($file)) {
                unlink($file);
            }
        }


        // step 2 : on détruit les fichiers en base

        $sql = "DELETE FROM `fichiers` WHERE `prof_id`=:PROFID";
        $q = $this->_db->prepare($sql);
        $q->bindValue(':PROFID', $arg, PDO::PARAM_INT) ;
        $statut = (bool)$q->execute() ;

        return $statut ;
    }

    /**
     * apiDeleteAllStudent
     *
     * détruire les documents (CNI, photo) et les fichiers associés
     *
     * les fichiers physiques ne sont détruits que si les fichiers sont détruits en base.
     *
     * @param  mixed $arg
     * @return bool
     */
    public function apiDeleteAllStudent(int $arg, string $domaine=""): bool
    {
        // ATTENTION, $arg ne doit pas être = 0 !
        // sinon on détruit tous les fichiers prof

        //$arg ne peut être null ou égal à 0
        if (!is_numeric($arg) || $arg===0) {
            trigger_error('FichiersManager::deleteAllStudent : la valeur reçue n\'est pas de type numerique', E_USER_WARNING);
            return 0;
        }

        // step 1 : on détruit les fichiers physiques

        $sql_select = "SELECT `uniq_id` FROM `fichiers` WHERE `candidat_id`=:STUDENTID";
        $select_stmt = $this->_db->prepare($sql_select);
        $select_stmt->bindValue(':STUDENTID', $arg, PDO::PARAM_INT) ;
        $statut2 = $select_stmt->execute() ;
        // on récupère le tableau
        $tabfic = $select_stmt->fetchAll(PDO::FETCH_ASSOC) ;

        $myrootDir = $_SERVER['DOCUMENT_ROOT'];
        //$domaine = AmlTools::getSubDomain();
        $domaine = $this->_domain;

        foreach ($tabfic as $t) {
            // détruire le fichier

            $file = join(DIRECTORY_SEPARATOR, array($myrootDir, 'Files', $domaine, $t['uniq_id']));

            if (file_exists($file)) {
                unlink($file);
                if (isset($this->_logger) && $this->_logger != null) {
                    $this->_logger->info("deleteAllStudent::File deleted");
                }
            } else {
                if (isset($this->_logger) && $this->_logger != null) {
                    $this->_logger->error("File not found for deletion");
                }
            }
        }

        // step 2 : on détruit les fichiers en base

        $sql_delete = "DELETE FROM `fichiers` WHERE `candidat_id`=:STUDENTID";
        $delete_stmt = $this->_db->prepare($sql_delete);
        $delete_stmt->bindValue(':STUDENTID', $arg, PDO::PARAM_INT) ;
        $statut = (bool)$delete_stmt->execute() ;

        return (bool) $statut ;
    }

    /**
     * deleteMusiquePassage
     *
     * @param  mixed $id
     * @return boolean
     */
    public function apiDeleteMusiquePassage(int $id, string $domaine="")
    {

        //$arg ne peut être null ou égal à 0
        if (!is_numeric($id) || $id===0) {
            trigger_error('FichiersManager::deleteMusiquePassage : la valeur reçue n\'est pas de type numerique', E_USER_WARNING);
            return false;
        }

        // step 1 : on détruit les fichiers physiques

        $sql_select = "SELECT `uniq_id` FROM `fichiers` WHERE `passage_id`=:ID ;";

        $select_stmt = $this->_db->prepare($sql_select);
        $select_stmt->bindValue(':ID', $id, PDO::PARAM_INT) ;
        $statut2 = $select_stmt->execute() ;
        // on récupère le tableau
        $tabfic = $select_stmt->fetchAll(PDO::FETCH_ASSOC) ;

        $myrootDir = $_SERVER['DOCUMENT_ROOT'];
        //$domaine = AmlTools::getSubDomain();
        $domaine = $this->_domain;

        foreach ($tabfic as $t) {
            // détruire le fichier

            $file = join(DIRECTORY_SEPARATOR, array($myrootDir, 'Files', $domaine, $t['uniq_id']));
            if (file_exists($file)) {
                unlink($file);
                if (isset($this->_logger) && $this->_logger != null) {
                    $this->_logger->info("deleteMusiquePassage::File deleted");
                }
            } else {
                if (isset($this->_logger) && $this->_logger != null) {
                    $this->_logger->error("File not found for deletion");
                }
            }
        }


        // step 2 : on détruit les fichiers en base

        $sql_file = "DELETE FROM `fichiers` WHERE `passage_id`=:ID ;";
        $file_stmt = $this->_db->prepare($sql_file);
        $file_stmt->bindValue(':ID', $id, PDO::PARAM_INT) ;
        $statut = (bool)$file_stmt->execute() ;

        // step 3 : on devalide le mp3
        // $arg=item
        $tableNature="passage";

        $sql_passage = 'UPDATE `'.$tableNature.'` SET `mp3` = 0 WHERE `id`=:ID;';
        $passage_stmt = $this->_db->prepare($sql_passage);
        $passage_stmt->bindValue(':ID', $id, PDO::PARAM_INT) ;
        $passage_stmt->execute() ;

        return (bool) $statut ;
    }


    /**
     * apiEntryMusicDelete
     *
     * @param  int $id
     * @param  int $nature_id
     * @return bool
     */
    public function apiEntryMusicDelete(int $id, int $nature_id): bool
    {
        global $domaine;

        // ind507, la nuit

        //$arg ne peut être null ou égal à 0
        if (!is_numeric($id) || $id===0) {
            trigger_error('FichiersManager::apiEntryMusicDelete : la valeur reçue de id n\'est pas de type numerique', E_USER_WARNING);
            return false;
        }
        if (!is_numeric($nature_id) || $nature_id===0) {
            trigger_error('FichiersManager::apiEntryMusicDelete : la valeur reçue de nature_id n\'est pas de type numerique', E_USER_WARNING);
            return false;
        }

        // step 1 : on détruit les fichiers physiques

        //$sql_select = "SELECT `uniq_id` FROM `fichiers` WHERE `passage_id`=:ID ;";
        $sql_select = "SELECT `uniq_id` FROM `fichiers` WHERE `passage_id`=:ID AND `typedoc`=:DOCID ;";

        $select_stmt = $this->_db->prepare($sql_select);
        $select_stmt->bindValue(':ID', $id, PDO::PARAM_INT) ;
        $select_stmt->bindValue(':DOCID', ($nature_id+4), PDO::PARAM_INT) ;
        $statut2 = $select_stmt->execute() ;
        // on récupère le tableau
        $tabfic = $select_stmt->fetchAll(PDO::FETCH_ASSOC) ;
        foreach ($tabfic as $t) {
            // détruire le fichier
            $myrootDir = $_SERVER['DOCUMENT_ROOT'];
            //$domaine = AmlTools::getSubDomain();

            $file = join(DIRECTORY_SEPARATOR, array($myrootDir, 'Files', $domaine, $t['uniq_id']));
            if (file_exists($file)) {
                unlink($file);
                if (isset($this->_logger) && $this->_logger != null) {
                    $this->_logger->info("apiEntryMusicDelete::File deleted");
                }
            } else {
                if (isset($this->_logger) && $this->_logger != null) {
                    $this->_logger->error("apiEntryMusicDelete::File not found for deletion");
                }
            }
        }


        // step 2 : on détruit les fichiers en base

        $sql_file = "DELETE FROM `fichiers` WHERE `passage_id`=:ID AND `typedoc`=:DOCID ;";
        $file_stmt = $this->_db->prepare($sql_file);
        $file_stmt->bindValue(':ID', $id, PDO::PARAM_INT) ;
        $file_stmt->bindValue(':DOCID', ($nature_id+4), PDO::PARAM_INT) ;
        $statut = (bool)$file_stmt->execute() ;

        // step 3 : on devalide le mp3
        // $arg=item
        //$tableNature="passage";
        $tableNature=$this->tableFromNatureId($nature_id);


        $sql_passage = 'UPDATE `'.$tableNature.'` SET `mp3` = 0 WHERE `id`=:ID;';
        $passage_stmt = $this->_db->prepare($sql_passage);
        $passage_stmt->bindValue(':ID', $id, PDO::PARAM_INT) ;
        $passage_stmt->execute() ;

        return (bool) $statut ;
    }

    // public function delOneFileByIdAndType( int $id, string $column, int $doc_type ) : int {
    // 	return 0;
    // }

    /**
     * apiFileDelete
     *
     * @param  int $id
     * @param  string $column
     * @param  int $doc_type
     * @return int
     */
    public function delOneFileByIdAndType(int $id, string $column, int $doc_type): int
    {
        if (!is_numeric($id) || $id===0) {
            trigger_error('FichiersManager::apiFileDelete : la valeur reçue de id n\'est pas de type numerique', E_USER_WARNING);
            return -1;
        }

        if (!is_numeric($id) || $id===0) {
            trigger_error('FichiersManager::apiFileDelete : la valeur reçue de doc-type n\'est pas de type numerique', E_USER_WARNING);
            return -1;
        }

        // TODO: detruire les fichiers physiques

        $count = 0;
        $sql_file="DELETE FROM `fichiers` WHERE `$column`=:ID AND typedoc=:DOCTYPE" ;
        $file_stmt = $this->_db->prepare($sql_file);
        $file_stmt->bindValue(':ID', $id, PDO::PARAM_INT) ;
        $file_stmt->bindValue(':DOCTYPE', $doc_type, PDO::PARAM_INT) ;
        $bStatus = $file_stmt->execute();

        if ($bStatus) {
            $count = $file_stmt->rowCount();
        } else {
            $count = -1;
        }

        return $count;
    }

    public function apiFileDeleteById(int $id): int
    {
        if (!is_numeric($id) || $id===0) {
            trigger_error('FichiersManager::apiFileDeleteById : la valeur reçue de id n\'est pas de type numerique', E_USER_WARNING);
            return -1;
        }

        // TODO: detruire les fichiers physiques

        $count = 0;
        $sql_file="DELETE FROM `fichiers` WHERE `id`=:ID" ;
        $file_stmt = $this->_db->prepare($sql_file);
        $file_stmt->bindValue(':ID', $id, PDO::PARAM_INT) ;
        $bStatus = $file_stmt->execute();

        if ($bStatus) {
            $count = $file_stmt->rowCount();
        } else {
            $count = -1;
        }

        return $count;
    }

    /**
     * tableFromNatureId
     *
     * @param  int $nature_id
     * @return string
     */
    private function tableFromNatureId(int $nature_id): string
    {
        switch ($nature_id) {
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

    /**
     * @param array $input
     * @param array $tr replacement array
     * @return array
     */
    private function replaceKeys(array $input, $tr)
    {
        $myArray=array();
        foreach ($input as $subArray) {
            $myArray[] = array_combine(preg_replace(array_map(
                function ($s) {
                return "/^$s$/";
            },
                array_keys($tr)
            ), $tr, array_keys($subArray)), $subArray);
        }
        return $myArray;
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
