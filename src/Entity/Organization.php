<?php

namespace App\Entity;

use App\Repository\OrganizationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=OrganizationRepository::class)
 * @ORM\Table(name="organizations")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("registration_number")
 */
class Organization
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=false, unique=true)
     */
    private $registration_number;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=false)
     */
    private $abbreviated_name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=false)
     */
    private $full_name;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $base_contact_person;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $base_working_hours;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registration_number;
    }

    public function setRegistrationNumber(string $registration_number): self
    {
        $this->registration_number = $registration_number;

        return $this;
    }

    public function getAbbreviatedName(): ?string
    {
        return $this->abbreviated_name;
    }

    public function setAbbreviatedName(string $abbreviated_name): self
    {
        $this->abbreviated_name = $abbreviated_name;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->full_name;
    }

    public function setFullName(string $full_name): self
    {
        $this->full_name = $full_name;

        return $this;
    }

    public function getBaseContactPerson(): ?string
    {
        return $this->base_contact_person;
    }

    public function setBaseContactPerson(?string $base_contact_person): self
    {
        $this->base_contact_person = $base_contact_person;

        return $this;
    }

    public function getBaseWorkingHours(): ?string
    {
        return $this->base_working_hours;
    }

    public function setBaseWorkingHours(?string $base_working_hours): self
    {
        $this->base_working_hours = $base_working_hours;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updated_at = new \DateTime();
    }
}
