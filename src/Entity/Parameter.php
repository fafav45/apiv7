<?php

namespace App\Entity;

use OpenApi\Annotations\Property;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\Schema;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ParamRepository;


/**
 * @ORM\Entity(repositoryClass=ParamRepository::class)
 * @Schema(
 *  schema="Param",
 *  title="Param",
 *  description="Parameters Model"
 * )
 */
class Parameter
{
  var $fichier="";
  var $groupe="";
  var $item="";

  var $fichier_ini=array();
  const KEYVALUE = 'inscriptions';
  const KEYDESCR = 'description';
  const KEYTYPE = 'type';

  /**
 * @OA\Property(type="string")
 */
protected $param;

/**
 * @OA\Property(type="string")
 */
protected $valeur;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @OA\Property(type="integer")
     */
private $id;
  

  function m_fichier($arg) // fichier.ini
  {
     $this->fichier=$arg;
     $this->fichier_ini=null;
     $this->fichier_ini=array();
     // Pour utiliser parse_ini_file() par défaut, enlevez /* et */, sinon supprimez ce commentaire (conseillé)
     /*if(false!==($array=@parse_ini_file($arg, TRUE)))
     {
        $this->fichier_ini=$array;
     }
     else*/
	 //if(file_exists($arg) && $fichier_lecture=file($arg)) // si le fichier exist et est accessible en lecture
    if(file_exists($arg) && $fichier_lecture=file($arg,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) // si le fichier exist et est accessible en lecture
     {
       $groupe_curseur="inscriptions";
       foreach($fichier_lecture as $ligne) // on lit chaque ligne
       {
         $ligne_propre=trim($ligne); // trim

         if(preg_match("#^\[(.+)\]$#",$ligne_propre,$matches))
         {
            $groupe_curseur=$matches[1]; // groupe ini [inscriptions]
         }
         else
         {
           if($ligne_propre[0]!=';' && $tableau=explode("=",$ligne,2)) // format de la ligne sans commentaire
           {
             // modif, on supprime les espaces de la clé
             //$this->fichier_ini[$groupe_curseur][$tableau[0]] = rtrim($tableau[1],"\n\r");
             $this->fichier_ini[$groupe_curseur][trim($tableau[0])]=ltrim(rtrim($tableau[1],"\n\r")); // on rempli le tableau [groupe][variable]
           }
         }
       }
     }
     //$this->valeur=$this->fichier_ini[$this->groupe][$this->item];
  }
  
  function c_groupe($arg)
  {
      $this->groupe=$arg;
      return true;
  }
  
  function m_groupe($arg)
  {
     $this->groupe=$arg;
     
     // 2020-12-08
     if( isset( $this->fichier_ini[$this->groupe][$this->item] ))
        $this->valeur = $this->fichier_ini[$this->groupe][$this->item];
     else 
     {
         $this->valeur = '';
         //trigger_error ( 'Parameter error: '.$this->item . ' site ' . $_SESSION['sousdomaine'] , E_USER_NOTICE );
     }
     
     return true;
  }
  
  function m_item($arg)
  {
     $this->item=$arg;
     $this->valeur=$this->fichier_ini[$this->groupe][$this->item];
     return true;
  }
  
  function m_put($arg, $arg_i=false, $arg_g=false, $arg_f=false)
  {
	  // arg_f : fichier
	  // arg_g : groupe
	  // arg_i : item
	  // arg : valeur
     if($arg_f!==false) $this->m_fichier($arg_f);
     if($arg_g!==false) $this->m_groupe($arg_g);
     if($arg_i!==false) $this->m_item($arg_i);
     $this->fichier_ini[$this->groupe][$this->item]=$arg;
     $this->valeur=$arg;
     return $this->fichier." ==> [".$this->groupe."] ".$this->item."=".$this->valeur;
  }
  
  function m_count($arg_gr=false)
  {
     if($arg_gr===false)
     return array(1=>$gr_cou=count($this->fichier_ini), 0=>$itgr_cou=count($this->fichier_ini, COUNT_RECURSIVE), 2=>$itgr_cou-$gr_cou);
     else
     return count($this->fichier_ini[$arg_gr]);
  }
  
  function array_groupe($arg_gr="inscriptions")
  {
     if($arg_gr===false)
     $arg_gr=$this->groupe;
     return $this->fichier_ini[$arg_gr];
  }
  
	function arrayParam()
	{

		$myArray = array();

		$tabvaleur = $this->fichier_ini[Parameter::KEYVALUE];
		$tabdescr = $this->fichier_ini[Parameter::KEYDESCR];
		$tabtype = $this->fichier_ini[Parameter::KEYTYPE];

		foreach($tabvaleur as $key => $value)
		{
			// tester que ces valeurs existent : in_array
			if (array_key_exists($key, $tabdescr)) {
				$theDescr=$tabdescr[$key];
			} else {$theDescr="";}
			if (array_key_exists($key, $tabtype)) {
				$theType=$tabtype[$key];
			} else {$theType="string";}
			$myArray[] = array("cle" => $key, "valeur" => $value, "description" => $theDescr, "type" => $theType) ;
		}  
		return $myArray;
	}
  
  
  function clear()
  {
      $this->fichier="";
      $this->groupe="";
      $this->item="";
      $this->valeur="";
      $this->fichier_ini=null;
      $this->fichier_ini=array();
  }
  function s_fichier()
  {
     $return=$this->fichier;
     if(file_exists($this->fichier)) unlink($this->fichier);
     $this->fichier="";
     $this->valeur="";
     return "fichier(".$return.") supprim&eacute;.";
  }
  function s_groupe()
  {
     $return=$this->groupe;
     if(isset($this->fichier_ini[$this->groupe])) unset($this->fichier_ini[$this->groupe]);
     $this->groupe="";
     $this->valeur="";
     return "groupe(".$return.") supprim&eacute;.";
  }
  function s_item()
  {
     $return=$this->item;
     if(isset($this->fichier_ini[$this->groupe][$this->item])) unset($this->fichier_ini[$this->groupe][$this->item]);
     $this->item="";
     $this->valeur="";
     return "item(".$return.") supprim&eacute;.";
  }
  function print_curseur()
  {
     echo "Fichier : <b>".$this->fichier."</b><br />";
     echo "Groupe : <b>".$this->groupe."</b><br />";
     echo "Item : <b>".$this->item."</b><br />";
     echo "Valeur : <b>".$this->valeur."</b><br />";
     return true;
  }
  

  function m_valeur($arg_item, $arg_groupe)
  {
     return $this->fichier_ini[$arg_groupe][$arg_item];
  }
  
}
?>