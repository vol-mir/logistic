<?php

namespace App\Entity;

use App\Repository\OrganizationRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\NotBlank
     * @Assert\Length(max=190)
     */
    private $registration_number;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=false)
     * @Assert\NotBlank
     * @Assert\Length(max=190)
     */
    private $abbreviated_name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=false)
     * @Assert\NotBlank
     * @Assert\Length(max=190)
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
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @var Address
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Address", mappedBy="organization", cascade={"remove"})
     */
    private $addresses;

    /**
     * @var TaskGoods
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TaskGoods", mappedBy="organization", cascade={"remove"})
     */
    private $tasks_goods;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->tasks_goods = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->abbreviated_name;
    }

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

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updated_at;
    }

    /**
     * @return Collection|Address[]
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses[] = $address;
            $address->setOrganization($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            // set the owning side to null (unless already changed)
            if ($address->getOrganization() === $this) {
                $address->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updated_at = new DateTime();
    }

    /**
     * @return Collection|TaskGoods[]
     */
    public function getTasksGoods(): Collection
    {
        return $this->tasks_goods;
    }

    public function addTasksGood(TaskGoods $task_goods): self
    {
        if (!$this->tasks_goods->contains($task_goods)) {
            $this->tasks_goods[] = $task_goods;
            $task_goods->setOrganization($this);
        }

        return $this;
    }

    public function removeTasksGood(TaskGoods $task_goods): self
    {
        if ($this->tasks_goods->contains($task_goods)) {
            $this->tasks_goods->removeElement($task_goods);
            // set the owning side to null (unless already changed)
            if ($task_goods->getOrganization() === $this) {
                $task_goods->setOrganization(null);
            }
        }

        return $this;
    }

}
