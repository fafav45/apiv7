<?php

namespace App\Repository;

use PDO;


use App\Entity\Student;
use App\Manager\FileManager;
use App\Manager\StudentManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


//class StudentRepository extends ServiceEntityRepository
class StudentRepository
{
    protected $bdd;
    protected $cnx;



    const SEARCHVAL = array("Nombre","Ecole_ID", "prenom", "Prenom", "nom", "Nom", "Ecole", "Sexe", "Titre","Categorie",
    "Classe","Professeur","Piste","Duree","Morceau","Auteur","Age_Ans","Age_Mois",
    "date_naissance","cni","photo","fichier_musique","atmin","atmaj",
	"Telephone","CD_Recu","Jour","Num_Passage","Remarque",
	"Heure_Passage","Heure_Convoc");
	
    const REPLACEVAL = array("Number","School_ID", "FirstName", "FirstName", "LastName","LastName", "School", "Gender","Title","Category",
    "Division","Teacher","Track","Duration","Song","Author","Age_Year","Age_Month",
    "Date_of_Birth","Id","IdPhoto","Music","minCert","majCert",
	"Phone number","MP3_received","Day","Entry number","Remark",
	"Entry hour","Summon hour");

    // public function __construct(ManagerRegistry $registry)
    // {
    //     parent::__construct($registry, Student::class);
    // }
    
    // studentGetAll($this->startDate, $this->birthdayValidated, $this->photoValidated, $this->member , $this->boardMember, false, $this->usedOnly);
    public function studentGetAll(?string $startDate, bool $birthdayValidated, bool $photoValidated, bool $member, bool $boardMember, bool $usedOnly) : ?array {
        $studentMgr = new StudentManager($this->cnx);
        $studentList = $studentMgr->apiGetAll($startDate, $birthdayValidated, $photoValidated, $member, $boardMember, $usedOnly);
        return $studentList;
    }


    public function studentGetById(int $id=0, string $startDate) : ?array {
        $studentMgr = new StudentManager($this->cnx);
        $studentList = $studentMgr->apiGet($id, $startDate);
        return $studentList;

    }


    public function studentUpdate(int $id, string $type, $value) : int {
        $studentMgr = new StudentManager($this->cnx);
        switch ($type) {
            case 'CNDPAID':
                $iStatus = $studentMgr->apiUpdate($id, 'pcnd', $value, 'string');
                break;
            case 'INDPAID':
                $iStatus = $studentMgr->apiUpdate($id, 'pind', $value, 'string');
                break;
            case 'DUOPAID':
                $iStatus = $studentMgr->apiUpdate($id, 'pduo', $value, 'string');
                break;
            case 'GRPPAID':
                $iStatus = $studentMgr->apiUpdate($id, 'pgrp', $value, 'string');
                break;
            case 'VALCNI':
                $iStatus = $studentMgr->apiUpdate($id, 'valide', $value, 'int');
                break;  
            case 'VALPHOTO':
                $iStatus = $studentMgr->apiUpdate($id, 'photo', $value, 'int');
                break;
            case 'VALMINOR':
                $iStatus = $studentMgr->apiUpdate($id, 'atmin', $value, 'int');
                break;
            case 'VALMAJOR':
                $iStatus = $studentMgr->apiUpdate($id, 'atmaj', $value, 'int');
                break;
            case 'VALCARD':
                $iStatus = $studentMgr->apiUpdate($id, 'carte', $value, 'int');
                break;
            case 'COMMENT':
                $iStatus = $studentMgr->apiUpdate($id, 'comment', $value, 'string');
                break;
        }
        return $iStatus;
    }


    public function studentPut(int $id, string $type, $value, $typeOf) : int {
        $studentMgr = new StudentManager($this->cnx);
        $iStatus = $studentMgr->apiUpdate($id, $type, $value, $typeOf);
        return $iStatus;
    }

    public function studentDelete(int $id) : bool {
        // on tente de supprimer, on instancie StudentManager & FichiersManager
        $myStudentMgr = new StudentManager($this->cnx);
        $myFileMgr = new FileManager($this->cnx);
        $bStatus1 = $myStudentMgr->apiDelete($id);
        $bStatus2 = $myFileMgr->apiDeleteAllStudent($id, $this->cnx->getSubDomain());
        unset($myStudentMgr);unset($myFileMgr);
        $s = $bStatus1 && $bStatus2;
        return $bStatus1;
    }

    
    /**
     * apiGetFollowUp
     *
     * @return array of adhesions
     */
    public function apiGetFollowUp() : ?array {
        $studentMgr = new StudentManager($this->cnx);
        $studentList = $studentMgr->apiFollowUp();
        return $studentList;
    }
    
    /**
     * apiGetAllFollowUp
     *
     * @return array of all students for register followUp
     */
    public function apiGetAllFollowUp() : ?array {
        $studentMgr = new StudentManager($this->cnx);
        $studentList = $studentMgr->apiAllFollowUp();
        return $studentList;
    }

    public function apiBoardMemberFollowUp() : ?array {
        $studentMgr = new StudentManager($this->cnx);
        $studentList = $studentMgr->apiBoardMemberFollowUp();
        return $studentList;
    }

    public function apiCsv(string $language, string $startDateSql) {
        $studentMgr = new StudentManager($this->cnx);
        $studentList = $studentMgr->apiCsv($language, $startDateSql);
        $rawData = $this->formatOutput( $studentList , $language) ;
        return $rawData;
    }
        

    public function apiDuplicates() : ?array {
        $studentMgr = new StudentManager($this->cnx);
        $studentList = $studentMgr->apiDuplicates();
        return $studentList;
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

    private function formatOutput( $q , $SetDisplayLang)
    {
        $nbcol = $q->columnCount() ;
        $xls_output = "" ;
        $xls_output_l = "" ;
        
        // entete
        for ($i = 0 ; $i < $nbcol ; $i++) {
            $entete = $q->getColumnMeta($i) ;
    
            if ($SetDisplayLang === 'en_IL' OR $SetDisplayLang ==='en_EN') {
                $nom_entete = '"' . $this->replaceCol($entete['name']) . '"' ;
            } else {
                $nom_entete = '"'.$entete['name'].'"' ;
            }
            // on ajoute chaque colonne à l'entète
            $xls_output_l .= $nom_entete . ";" ;
        }

        // ajout de la colonne Manque à l'entète
        if ($SetDisplayLang === 'en_IL' OR $SetDisplayLang ==='en_EN') {
            $xls_output_l .= '"' . $this->replaceCol("Manque") . '"' . ';' ;
        } else {
            $xls_output_l .= '"' . "Manque" . '"' . ';' ;
        }

        // 1ere ligne de la sortie = entete
        $xls_output .= $xls_output_l . "\n" ;
        
        // données
        while ($listage = $q->fetch()) {
            $xls_output_l = "" ;
            for ($i = 0 ; $i < $nbcol ; $i++) {
                $xls_output_l .= $listage[$i] . ";" ;
            }
            $xls_output .= $xls_output_l . ";\n" ;
        }
        return ($xls_output) ;
        
    }

    private function replaceCol(string $arg) : string
    {
        $res = str_replace(SELF::SEARCHVAL, SELF::REPLACEVAL, $arg);
        return $res;
    }

}
