<?php

namespace App\Entity;

use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use OpenApi\Annotations as OA;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SchoolRepository;

/**
 * @ORM\Entity(repositoryClass=SchoolRepository::class)
 * @Schema(
 * schema="Schools",
 * title="Schools",
 * description="School Model"
 * )
 */
class School
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @OA\Property(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string")
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string")
     */
    private $additionalAddress;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string")
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=10)
     * @OA\Property(type="string")
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string")
     */
    private $email;

    /**
     * @ORM\Column(type="integer")
     * @OA\Property(type="integer")
     */
    private $readonly;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @OA\Property(type="string")
     */
    private $validationDate;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @OA\Property(type="string")
     */
    private $lastConnectionDate;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string")
     */
    private $userName;

    /**
     * @ORM\Column(type="integer")
     * @OA\Property(type="integer")
     */
    private $count;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAdditionalAddress(): ?string
    {
        return $this->additionalAddress;
    }

    public function setAdditionalAddress(string $additionalAddress): self
    {
        $this->additionalAddress = $additionalAddress;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getReadonly(): ?int
    {
        return $this->readonly;
    }

    public function setReadonly(int $readonly): self
    {
        $this->readonly = $readonly;

        return $this;
    }

    public function getValidationDate(): ?string
    {
        return $this->validationDate;
    }

    public function setValidationDate(?string $validationDate): self
    {
        $this->validationDate = $validationDate;

        return $this;
    }

    public function getLastConnectionDate(): ?string
    {
        return $this->lastConnectionDate;
    }

    public function setLastConnectionDate(?string $lastConnectionDate): self
    {
        $this->lastConnectionDate = $lastConnectionDate;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }
}
