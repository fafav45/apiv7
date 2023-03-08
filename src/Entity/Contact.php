<?php

namespace App\Entity;

use OpenApi\Annotations as OA;
use OpenApi\Annotations\Schema;
use OpenApi\Annotations\Property;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass=ContactRepository::class)
 * @Schema(
 *  schema="Contacts",
 * 	title="Contacts",
 * 	description="Contact Model"
 * )
 */
class Contact
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
    private $id_ecole;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string")
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=16)
     * @OA\Property(type="string", property="phoneNumber")
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @OA\Property(type="string")
     */
    private $teacherEmail;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string")
     */
    private $schoolName;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string")
     */
    private $schoolEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @OA\Property(type="string")
     */
    private $city;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdEcole(): ?int
    {
        return $this->id_ecole;
    }

    public function setIdEcole(int $id_ecole): self
    {
        $this->id_ecole = $id_ecole;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getTeacherEmail(): ?string
    {
        return $this->teacherEmail;
    }

    public function setTeacherEmail(?string $teacherEmail): self
    {
        $this->teacherEmail = $teacherEmail;

        return $this;
    }

    public function getSchoolName(): ?string
    {
        return $this->schoolName;
    }

    public function setSchoolName(string $schoolName): self
    {
        $this->schoolName = $schoolName;

        return $this;
    }

    public function getSchoolEmail(): ?string
    {
        return $this->schoolEmail;
    }

    public function setSchoolEmail(string $schoolEmail): self
    {
        $this->schoolEmail = $schoolEmail;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }
}
