<?php

namespace App\Repository;

use PDO;
use App\Entity\Contact;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

//class ContactRepository extends ServiceEntityRepository
class ContactRepository
{
    protected $bdd;
    protected $cnx;
    protected $nomfree;
    const SEARCHVAL = array("id_ecole", "prenom", "nom",  "telephone", "courriel_prof", "courriel_ecole", "ville", "ecole");
    const REPLACEVAL = array("School_ID", "FirstName", "LastName",  "Phone Number", "Teacher email", "School email", "City","School");

    // public function __construct(ManagerRegistry $registry)
    // {
    //     parent::__construct($registry, Contact::class);
    // }

    public function getAll(Connexion $cnx) : string {

        $sql = "
        (SELECT P.`id_ecole`, P.`nom`, P.`prenom`, P.`telephone`, P.`email` AS 'courriel_prof', E.`nom` AS 'ecole', E.`email` AS 'courriel_ecole', E.`ville`  FROM `professeur` P
            INNER JOIN `ecole`E
              ON P.`id_ecole`=E.`id`
            WHERE P.`cnd`=1)
        UNION
        (SELECT C.`id_ecole`, C.`nom`, C.`prenom`, C.`telephone1` AS 'telephone', C.`email` AS 'courriel_prof', 'CANDIDAT LIBRE' AS 'ecole', '' AS 'courriel_ecole', C.`ville` FROM `candidat` C
            INNER JOIN `ecole` E
                ON C.`id_ecole`=E.`id`
        WHERE E.`nom`='$this->nomfree' AND C.`cnd`=1)
        ";

        $stmt = $cnx->getBdd()->query($sql) ;
        $rawData = $this->formatOutput( $stmt , $cnx->getLanguage()) ;

        return $rawData;
    }

    public function setBdd(PDO $arg)
    {
        $this->bdd = $arg;
    }

    public function setConnexion(Connexion $arg)
    {
        $this->cnx = $arg;
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
            $xls_output_l .= $nom_entete . ";" ;
        }
        $xls_output .= $xls_output_l . "\n" ;
        
        // données
        while ($listage = $q->fetch()) {
            $xls_output_l = "" ;
            for ($i = 0 ; $i < $nbcol ; $i++) {
                $xls_output_l .= $listage[$i] . ";" ;
            }
            $xls_output .= $xls_output_l . "\n" ;
        }
        return ($xls_output) ;
        
    }

    private function replaceCol(string $arg) : string
    {
        $res = str_replace(SELF::SEARCHVAL, SELF::REPLACEVAL, $arg);
        return $res;
    }
}
