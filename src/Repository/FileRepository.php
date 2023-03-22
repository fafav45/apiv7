<?php

namespace App\Repository;

use PDO;
use App\Entity\File;
use App\Manager\FileManager;
use App\Repository\Connexion;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


//class FileRepository extends ServiceEntityRepository
class FileRepository
{
    protected $bdd;
    protected $cnx;

    // public function __construct(ManagerRegistry $registry)
    // {
    //     parent::__construct($registry, File::class);
    // }


    //public function findAllFiles(Connexion $cnx) : ?array{
    public function findAllFiles(Connexion $cnx, bool $musicsCountOnly, bool $certificatesCountOnly, bool $IDsCountOnly, bool $IDPhotosCountOnly, bool $mp3Only): ?array
    {
        $fileMgr = new FileManager($cnx);
        //apiGetAll(bool $musicsCountOnly=false, bool $certificatesCountOnly=false, bool $IDsCountOnly=false, bool $IDPhotosCountOnly=false, bool $getUsedOnly=false, bool $mp3Only=false) : array {
        $ar = $fileMgr->apiGetAll($musicsCountOnly, $certificatesCountOnly, $IDsCountOnly, $IDPhotosCountOnly, false, $mp3Only);

        return $ar;
    }

    public function findOneFile(Connexion $cnx, int $id, bool $withMD5): array
    {
        $fileMgr = new FileManager($cnx);
        $ar = $fileMgr->apiGet($id, $withMD5);

        return $ar;
    }

    public function delOneFileByIdAndType(Connexion $cnx, int $id, string $type): int
    {
        // test de $type
        // studentPhotoID or teacherPhotoID
        $authorizedTypes = array('studentPhotoID','teacherPhotoID');
        if (!in_array($type, $authorizedTypes)) {
            return -2;
        }

        $fileMgr = new FileManager($cnx);
        //$ar = $fileMgr->apiFileDelete($id, 'candidat_id', 2);

        // $candidat_id, string column, int $doc_type=photo_identité
        // column prof_id pour prof
        // doctype=2 pour photo
        // doctype=1 pour CNI
        // doctype=3,4 pour atestation
        // doctype=5,7 pour music ind

        switch ($type) {
            case 'studentPhotoID':
                $column = 'candidat_id';
                $doc_type = 2;
                break;
            case 'teacherPhotoID':
                $column = 'prof_id';
                $doc_type = 2;
                break;
            case 'studentCNI':
                $column = 'candidat_id';
                $doc_type = 1;
                break;
            case 'teacherCNI':
                $column = 'prof_id';
                $doc_type = 1;
                break;
            case 'studentMinorCertificate':
                $column = 'candidat_id';
                $doc_type = 3;
                break;
            case 'studentMajorCertificate':
                $column = 'candidat_id';
                $doc_type = 4;
                break;
        }

        $count = $fileMgr->delOneFileByIdAndType($id, $column, $doc_type);

        return $count;
    }

    public function delOneFileById(Connexion $cnx, int $id, bool $bPhysically=false)
    {

        $fileMgr = new FileManager($cnx);
        $count = $fileMgr->apiFileDeleteById($id);

        // si physically=true, alors on détruit également le fichier physique
        if($count===1 && $bPhysically===true) {}

        return $count;
    }


    public function setBdd(PDO $arg)
    {
        $this->bdd = $arg;
    }

    public function setConnexion(Connexion $arg)
    {
        $this->cnx = $arg;
    }
}
