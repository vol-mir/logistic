<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AddressRepository::class)
 * @ORM\Table(name="addresses")
 * @ORM\HasLifecycleCallbacks()
 */
class Address
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
     * @ORM\Column(type="string", length=190, nullable=false)
     */
    private $street;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=true)
     */
    private $district;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=false)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=false)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=false)
     */
    private $postcode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=false)
     */
    private $point_name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=true)
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=true)
     */
    private $locality;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization", inversedBy="addresses")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", nullable=false)
     */
    private $organization;

    /**
     * @var TaskGoods
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TaskGoods", mappedBy="address_office")
     */
    private $tasks_goods_address_office;

    /**
     * @var TaskGoods
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TaskGoods", mappedBy="address_goods_yard")
     */
    private $tasks_goods_address_goods_yard;

    public function __construct()
    {
        $this->tasks_goods_address_office = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getDistrict(): ?string
    {
        return $this->district;
    }

    public function setDistrict(?string $district): self
    {
        $this->district = $district;

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

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getPointName(): ?string
    {
        return $this->point_name;
    }

    public function setPointName(string $point_name): self
    {
        $this->point_name = $point_name;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function setLocality(?string $locality): self
    {
        $this->locality = $locality;

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

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getFullAddress(): ?string
    {
        return (!empty($this->point_name)?$this->point_name.' - ':'').(!empty($this->postcode)?$this->postcode.', ':'').(!empty($this->country)?$this->country.', ':'').
            (!empty($this->region)?$this->region.', ':'').(!empty($this->city)?$this->city.', ':'').
            (!empty($this->locality)?$this->locality.', ':'').(!empty($this->street)?$this->street.'':'');
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

    /**
     * @return Collection|TaskGoods[]
     */
    public function getTasksGoodsAddressOffice(): Collection
    {
        return $this->tasks_goods_address_office;
    }

    public function addTasksGoodsAddressOffice(TaskGoods $task_goods_address_office): self
    {
        if (!$this->tasks_goods_address_office->contains($task_goods_address_office)) {
            $this->tasks_goods_address_office[] = $task_goods_address_office;
            $task_goods_address_office->setAddressOffice($this);
        }

        return $this;
    }

    public function removeTasksGoodsAddressOffice(TaskGoods $task_goods_address_office): self
    {
        if ($this->tasks_goods_address_office->contains($task_goods_address_office)) {
            $this->tasks_goods_address_office->removeElement($task_goods_address_office);
            // set the owning side to null (unless already changed)
            if ($task_goods_address_office->getAddressOffice() === $this) {
                $task_goods_address_office->setAddressOffice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TaskGoods[]
     */
    public function getTasksGoodsAddressGoodsYard(): Collection
    {
        return $this->tasks_goods_address_goods_yard;
    }

    public function addTasksGoodsAddressGoodsYard(TaskGoods $task_goods_address_goods_yard): self
    {
        if (!$this->tasks_goods_address_goods_yard->contains($task_goods_address_goods_yard)) {
            $this->tasks_goods_address_goods_yard[] = $task_goods_address_goods_yard;
            $task_goods_address_goods_yard->setAddressGoodsYard($this);
        }

        return $this;
    }

    public function removeTasksGoodsAddressGoodsYard(TaskGoods $task_goods_address_goods_yard): self
    {
        if ($this->tasks_goods_address_goods_yard->contains($task_goods_address_goods_yard)) {
            $this->tasks_goods_address_goods_yard->removeElement($task_goods_address_goods_yard);
            // set the owning side to null (unless already changed)
            if ($task_goods_address_goods_yard->getAddressGoodsYard() === $this) {
                $task_goods_address_goods_yard->setAddressGoodsYard(null);
            }
        }

        return $this;
    }
}
