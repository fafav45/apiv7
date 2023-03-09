<?php

namespace App\Repository;

use PDO;
use App\Entity\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Parameter>
 *
 * @method School|null find($id, $lockMode = null, $lockVersion = null)
 * @method School|null findOneBy(array $criteria, array $orderBy = null)
 * @method School[]    findAll()
 * @method School[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParamRepository extends ServiceEntityRepository
{
    protected $bdd;
    protected $cnx;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parameter::class);
    }
    
    // function param

    public function getParamByName(Connexion $cnx, string $name) : ?array {
 
        $fichierini = $cnx->getIniRegion();

        // paramètres recuperes depuis classe Parametre
        $oIni=new Parameter();
        $oIni->m_fichier($fichierini);
        $tabParam = $oIni->array_groupe("inscriptions") ;
        unset($oIni);


        if (array_key_exists($name, $tabParam)) {
            $rawData[] = array('param' => $name, 'valeur' => $tabParam[$name]);
        } else {
            $rawData=[];                               
        }

        return $rawData;
    }

    public function getAllParams(Connexion $cnx) : ?array {
   
        $fichierini = $cnx->getIniRegion();

        // paramètres recuperes depuis classe Parametre
        $oIni=new Parameter();

        $oIni->m_fichier($fichierini);
        $tabParam = $oIni->array_groupe("inscriptions") ;
        unset($oIni);

        $rawData=array();
                        
        foreach($tabParam as $key => $value)
        {
            $rawData[] = array('param' => $key, 'valeur' => $value);
        }

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
