<?php

namespace App\Entity;

use OpenApi\Annotations as OA;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FileRepository::class)
 * @OA\Schema(
 *  schema="Files",
 * 	title="Files",
 * 	description="File Model"
 * )
 */
class File
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @OA\Property(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @OA\Property(type="integer")
     */
    private $school_id;

    /**
     * @ORM\Column(type="integer")
     * @OA\Property(type="integer", description="0 if not concerned")
     */
    private $candidat_id;

    /**
     * @ORM\Column(type="integer")
     * @OA\Property(type="integer", description="0 if not concerned")
     */
    private $teacher_id;

    /**
     * @ORM\Column(type="integer")
     * @OA\Property(type="integer", description="0 if not concerned")
     */
    private $entry_id;

    /**
     * @ORM\Column(type="string", length=128)
     * @OA\Property(type="string", example="MAJ_S1133_6157814ce7253.PDF")
     */
    private $uniq_id;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string", example="ATMAJ_BAUR_Pauline.PDF")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=64)
     * @OA\Property(type="string", example="application/pdf")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=10)
     * @OA\Property(type="string", example="2021-10-01")
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     * @OA\Property(type="integer", example="4")
     */
    private $docType;

    /**
     * @ORM\Column(type="string", length=64)
     * @OA\Property(type="string", example="fc004876847f39950981460883cf4d3e")
     */
    private $md5;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string")
     */
    private $comment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSchoolId(): ?int
    {
        return $this->school_id;
    }

    public function setSchoolId(int $school_id): self
    {
        $this->school_id = $school_id;

        return $this;
    }

    public function getCandidatId(): ?int
    {
        return $this->candidat_id;
    }

    public function setCandidatId(int $candidat_id): self
    {
        $this->candidat_id = $candidat_id;

        return $this;
    }

    public function getTeacherId(): ?int
    {
        return $this->teacher_id;
    }

    public function setTeacherId(int $teacher_id): self
    {
        $this->teacher_id = $teacher_id;

        return $this;
    }

    public function getEntryId(): ?int
    {
        return $this->entry_id;
    }

    public function setEntryId(int $entry_id): self
    {
        $this->entry_id = $entry_id;

        return $this;
    }

    public function getUniqId(): ?string
    {
        return $this->uniq_id;
    }

    public function setUniqId(string $uniq_id): self
    {
        $this->uniq_id = $uniq_id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDocType(): ?int
    {
        return $this->docType;
    }

    public function setDocType(int $docType): self
    {
        $this->docType = $docType;

        return $this;
    }

    public function getMd5(): ?string
    {
        return $this->md5;
    }

    public function setMd5(string $md5): self
    {
        $this->md5 = $md5;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
