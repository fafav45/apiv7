<?php

namespace App\Repository;

use PDO;
use PDOException;

class Connexion
{
    private $_bdd = null;
    private $myrootDir;
    private $subDomain;
    private $confRegion;
    private $language;


    public function __construct()
    {
        $this->myrootDir = $_SERVER['DOCUMENT_ROOT'] ;
        $this->subDomain = $this->getCountryDomain();
        $this->confRegion = $this->myrootDir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$this->subDomain."_conf.php";

        if (!file_exists($this->confRegion)) {
            dd("Region Configuration file does not exist !");
        }

        require($this->confRegion);


        $this->language = $SetDisplayLang;

        try {
            $dsn = 'mysql:dbname='.$dbname.';host='.$dbhost.';charset=UTF8';
            $this->_bdd = new PDO($dsn, $dbuser, $dbpasswd);
        } catch (PDOException $e) {
            dd("Erreur connexion");
        }
    }

    public function getBdd()
    {
        return $this->_bdd;
    }

    public function getSubDomain()
    {
        return $this->subDomain;
    }

    public function getConfRegion()
    {
        return $this->confRegion;
    }

    public function getMyrootDir()
    {
        return $this->myrootDir;
    }

    private function getCountryDomain()
    {
        $splitUrl = parse_url($_SERVER['HTTP_HOST']); // localhost ou ins-demo.cnd.info ou www.ins-demo.cnd.info
        $segments = explode('.', $splitUrl['path']);

        // si existe CONTEXT_PREFIX et si non vide et si commence par / alors local on retourned trimmed

        if (isset($_SERVER['CONTEXT_PREFIX'])) {
            if (mb_strlen($_SERVER['CONTEXT_PREFIX']) > 0) {
                $trimmed = trim($_SERVER['CONTEXT_PREFIX'], "/");
                return $trimmed;
            }
        }

        switch (count($segments)) {
            case 3: // inscriptions-centre.cnd.info
                return $segments[0];

            case 4: // www.inscriptions-centre.cnd.info
                return $segments[1];

        }
    }

    /**
     * getLanguage
     * Get the value of language
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }


    /**
     * setLanguage
     * Set the value of language
     *
     * @param  string $language
     * @return void
     */
    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }
}
