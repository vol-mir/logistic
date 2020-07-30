<?php

namespace App\Entity;

use App\Repository\TaskGoodsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * @ORM\Entity(repositoryClass=TaskGoodsRepository::class)
 * @ORM\Table(name="tasks_goods")
 * @ORM\HasLifecycleCallbacks()
 */
class TaskGoods
{
    public const TON = 1;
    public const KILOGRAM = 2;
    public const GRAMME = 3;
    public const LITER = 4;

    public const LIST_UNITS = [
        self::TON => 'unit.ton',
        self::KILOGRAM => 'unit.kilogram',
        self::GRAMME => 'unit.gramme',
        self::LITER => 'unit.liter',
    ];

    public const FORMED = 1;
    public const TO_REVIEW = 2;
    public const PERFORMED = 3;
    public const REJECTED = 4;
    public const DONE = 5;
    public const NOT_DONE = 6;

    public const STATUSES = [
        self::FORMED => 'status.formed',
        self::TO_REVIEW => 'status.to_review',
        self::PERFORMED => 'status.performed',
        self::REJECTED => 'status.rejected',
        self::DONE => 'status.done',
        self::NOT_DONE => 'status.not_done',
    ];

    public const MANUAL = 1;
    public const TOP = 2;
    public const SIDE = 3;
    public const REAR = 4;

    public const LIST_LOADING_NATURES = [
        self::MANUAL => 'loading_natures.manual',
        self::TOP => 'loading_natures.top',
        self::SIDE => 'loading_natures.side',
        self::REAR => 'loading_natures.rear',
    ];

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
     * @ORM\Column(type="text", nullable=false)
     */
    private $goods;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=false, options={"default": 0})
     */
    private $weight = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false, options={"default": 1, "unsigned"=true})
     */
    private $unit = 1;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=true)
     */
    private $dimensions;

    /**
     * @var string
     *
     * @ORM\Column(type="integer", nullable=false, options={"default": 1, "unsigned"=true})
     */
    private $number_of_packages = 1;

    /**
     * @var string
     *
     * @ORM\Column(type="integer", nullable=false, options={"default": 1, "unsigned"=true})
     */
    private $loading_nature = 1;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $contact_person;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $working_hours;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false, options={"default": 1, "unsigned"=true})
     */
    private $status = 1;

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

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization", inversedBy="tasks_goods")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", nullable=false)
     */
    private $organization;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @var Address
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Address", inversedBy="tasks_goods_address_office")
     * @ORM\JoinColumn(name="address_office_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $address_office;

    /**
     * @var Address
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Address", inversedBy="tasks_goods_address_goods_yard")
     * @ORM\JoinColumn(name="address_goods_yard_id", referencedColumnName="id", nullable=false)
     */
    private $address_goods_yard;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $date_task_goods;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $report;

    /**
     * @var Driver[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Driver", inversedBy="taskGoods", cascade={"persist"})
     * @ORM\JoinTable(name="task_goods_driver")
     */
    private $drivers;

    /**
     * @var Transport[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Transport", inversedBy="taskGoods", cascade={"persist"})
     * @ORM\JoinTable(name="task_goods_transport")
     */
    private $transports;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tasks_goods")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    public function __construct()
    {
        $this->setDateTaskGoods(new \DateTime());
        $this->drivers = new ArrayCollection();
        $this->transports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGoods(): ?string
    {
        return $this->goods;
    }

    public function setGoods(string $goods): self
    {
        $this->goods = $goods;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getUnit(): ?int
    {
        return $this->unit;
    }

    public function setUnit(int $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getDimensions(): ?string
    {
        return $this->dimensions;
    }

    public function setDimensions(string $dimensions): self
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    public function getNumberOfPackages(): ?int
    {
        return $this->number_of_packages;
    }

    public function setNumberOfPackages(int $number_of_packages): self
    {
        $this->number_of_packages = $number_of_packages;

        return $this;
    }

    public function getLoadingNature(): ?int
    {
        return $this->loading_nature;
    }

    public function setLoadingNature(int $loading_nature): self
    {
        $this->loading_nature = $loading_nature;

        return $this;
    }

    public function getContactPerson(): ?string
    {
        return $this->contact_person;
    }

    public function setContactPerson(string $contact_person): self
    {
        $this->contact_person = $contact_person;

        return $this;
    }

    public function getWorkingHours(): ?string
    {
        return $this->working_hours;
    }

    public function setWorkingHours(string $working_hours): self
    {
        $this->working_hours = $working_hours;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

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
        $this->status = 1;
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updated_at = new \DateTime();
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getAddressOffice(): ?Address
    {
        return $this->address_office;
    }

    public function setAddressOffice(?Address $address_office): self
    {
        $this->address_office = $address_office;

        return $this;
    }

    public function getAddressGoodsYard(): ?Address
    {
        return $this->address_goods_yard;
    }

    public function setAddressGoodsYard(?Address $address_goods_yard): self
    {
        $this->address_goods_yard = $address_goods_yard;

        return $this;
    }

    public function getDateTaskGoods(): ?\DateTimeInterface
    {
        return $this->date_task_goods;
    }

    public function setDateTaskGoods(\DateTimeInterface $date_task_goods): self
    {
        $this->date_task_goods = $date_task_goods;

        return $this;
    }

    public function getReport(): ?string
    {
        return $this->report;
    }

    public function setReport(?string $report): self
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return Collection|Driver[]
     */
    public function getDrivers(): Collection
    {
        return $this->drivers;
    }

    public function addDriver(Driver $driver): self
    {
        if (!$this->drivers->contains($driver)) {
            $this->drivers[] = $driver;
        }

        return $this;
    }

    public function removeDriver(Driver $driver): self
    {
        if ($this->drivers->contains($driver)) {
            $this->drivers->removeElement($driver);
        }

        return $this;
    }

    /**
     * @return Collection|Transport[]
     */
    public function getTransports(): Collection
    {
        return $this->transports;
    }

    public function addTransport(Transport $transport): self
    {
        if (!$this->transports->contains($transport)) {
            $this->transports[] = $transport;
        }

        return $this;
    }

    public function removeTransport(Transport $transport): self
    {
        if ($this->transports->contains($transport)) {
            $this->transports->removeElement($transport);
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isAuthor(User $user = null)
    {
        return $user && $user->getId() == $this->getUser()->getId();
    }
}
