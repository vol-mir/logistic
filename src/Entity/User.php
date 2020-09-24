<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("username")
 */
class User implements UserInterface
{
    public const ROLES = [
        'ROLE_ADMIN' => 'ROLE_ADMIN',
        'ROLE_DISPATCHER' => 'ROLE_DISPATCHER',
        'ROLE_OPERATOR' => 'ROLE_OPERATOR',
    ];

    public const DEPARTMENTS = [
        12 => 'department.oge',
        15 => 'department.oit',
        18 => 'department.ogk',
        19 => 'department.ogt',
        21 => 'department.ogm',
        27 => 'department.omtsvk',
        29 => 'department.op',
        30 => 'department.rsmy',
        40 => 'department.om',
        45 => 'department.atc',
        48 => 'department.cil',
        57 => 'department.canteen'
    ];
    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     */
    protected $plainPassword;
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
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;
    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $roles = [];
    /**
     * @var string The hashed password
     *
     * @ORM\Column(type="string")
     */
    private $password;
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=true)
     */
    private $first_name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=true)
     */
    private $last_name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=true)
     */
    private $middle_name;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    private $department;

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
     * @var TaskGoods
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TaskGoods", mappedBy="user")
     */
    private $tasks_goods;

    public function __construct()
    {
        $this->tasks_goods = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(?string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middle_name;
    }

    public function setMiddleName(?string $middle_name): self
    {
        $this->middle_name = $middle_name;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->last_name . ' ' . mb_substr($this->first_name, 0, 1, "UTF-8") . '. ' . mb_substr($this->middle_name, 0, 1, "UTF-8") . '.';
    }


    public function getDepartment(): ?int
    {
        return $this->department;
    }

    public function setDepartment(?int $department): self
    {
        $this->department = $department;

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

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password): self
    {
        $this->plainPassword = $password;

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
            $task_goods->setUser($this);
        }

        return $this;
    }

    public function removeTasksGood(TaskGoods $task_goods): self
    {
        if ($this->tasks_goods->contains($task_goods)) {
            $this->tasks_goods->removeElement($task_goods);
            // set the owning side to null (unless already changed)
            if ($task_goods->getUser() === $this) {
                $task_goods->setUser(null);
            }
        }

        return $this;
    }
}
