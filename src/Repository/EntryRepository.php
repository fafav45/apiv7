<?php

namespace App\Repository;

use PDO;

use App\Entity\Entry;
use App\Manager\FileManager;
use Psr\Log\LoggerInterface;
use App\Manager\EntryManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


class EntryRepository extends ServiceEntityRepository
{
    protected $bdd;
    protected $cnx;
    protected $logger;

    const OVERGAUGE = 0.1;
    // "Nombre" doit être placé en premier, sinon conflit avec "Nom"
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

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Entry::class);
        $this->logger = $logger;
    }
    

    public function entryGetAll(bool $musicValidated, bool $countOnly=false, bool $sumOnly=false, string $nature, int $teacher_id=0, int $student_id=0, int $maxEntries=1000) : ?array {
        $manager = new EntryManager($this->cnx);
        $list = $manager->apiGetList($musicValidated, $countOnly, $sumOnly, $this->getNatureId($nature), $teacher_id, $student_id);

        if($sumOnly) { // pour gauge
            $truc = $list[0]['count'];
            unset($list);
            $list=array();
            array_push($list, array('label'=> 'gauge', 'count' => $truc));
            array_push($list, array('label'=> 'max', 'count' => ($maxEntries - $truc)));
            // 1 : enregistrés
            // 2 : max passages - enregistrés
        }

        return $list;
    }

    public function getMusicListPerNature(string $nature) : ?array {
        $manager = new EntryManager($this->cnx);
        $list = $manager->apiGetMusicListPerNatureId($this->getNatureId($nature));
        return $list;
    }

    public function apiGetListForJava(string $nature, bool $national) {
        $manager = new EntryManager($this->cnx);
        $list = $manager->apiGetListForJava($this->getNatureId($nature), $national);
        return $list;
    }

    public function apiCsv(string $setDisplayLang, string $datecalc='2023-01-01', string $region) : ?string{
        $manager = new EntryManager($this->cnx);
        $list = $manager->apiCsv($setDisplayLang, $datecalc, $region);
        $rawData = $this->formatOutput( $list , $setDisplayLang) ;
        return $rawData;
    }

    public function apiFollowUp() : ?array {
        $manager = new EntryManager($this->cnx);
        $list = $manager->apiFollowUp();
        return $list;
    }


    public function entryGetById(int $id=0, string $nature) : ?array {
        $manager = new EntryManager($this->cnx);
        $list = $manager->apiGet($id, $this->getNatureId($nature));
        return $list;
    }

    public function updateMP3Comment(int $id, string $nature, string $value) : int {

        $manager = new EntryManager($this->cnx);
        $iStatus = $manager->updateMP3Comment($id, $this->getNatureId($nature), $value);
        return $iStatus;
	}

    public function updateMP3Validation(int $id, string $nature, int $value) : int {

        $manager = new EntryManager($this->cnx);
        $iStatus = $manager->updateMP3Validation($id, $this->getNatureId($nature), $value);
        return $iStatus;
	}

    public function entryDelete(int $id, string $nature) : int {
        $manager = new EntryManager($this->cnx);
        $natureId = $this->getNatureId($nature);
        $this->logger->info("repository apiEntryDelete natureId: $natureId"); // -1
        $iStatus = $manager->apiEntryDelete($id, $natureId);
        $this->logger->info("repository apiEntryDelete status: $iStatus"); // -1
        return $iStatus;
    }





/*
    public function entryDelete(int $id) : bool {
        // on tente de supprimer, on instancie StudentManager & FichiersManager
        $entryMgr = new EntryManager($this->cnx);
        $myFileMgr = new FileManager($this->cnx);
        $bStatus1 = $entryMgr->apiDelete($id);
        $bStatus2 = $myFileMgr->apiDeleteAllStudent($id, $this->cnx->getSubDomain());
        unset($entryMgr);unset($myFileMgr);
        $s = $bStatus1 && $bStatus2;
        return $bStatus1;
    }
*/
    
    /**
     * apiGetFollowUp
     *
     * @return array of adhesions
     */
    public function apiGetFollowUp() : ?array {
        $manager = new EntryManager($this->cnx);
        $list = $manager->apiFollowUp();
        return $list;
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

    protected function getNatureId(?string $arg) : int{ // null accepté
        switch($arg) {
            case 'individual':
            case 'individuel':
            case 'ind':
                $natureId = 1;
            break;
            case 'duet':
            case 'duo':
                $natureId = 2;
            break;
            case 'group':
            case 'groupe':
            case 'grp':
                $natureId = 3;
            break;
            default:
            $natureId = 0;
        }
        return $natureId;
        
    }

	protected function tableFromNatureId(int $nature_id) : string {
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

}
