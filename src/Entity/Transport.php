<?php

namespace App\Entity;

use App\Repository\TransportRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransportRepository::class)
 * @ORM\Table(name="transports")
 * @ORM\HasLifecycleCallbacks()
 */
class Transport
{
    public const VAN_KIND = 1;
    public const ONBOARD_OPEN_KIND = 2;
    public const PICKUP_KIND = 3;
    public const ONBOARD_AWNING_KIND = 4;
    public const TRAILER_ONBOARD_AWNING_KIND = 5;
    public const TRACTOR_UNIT_KIND = 6;
    public const SEMITRAILER_AWNING_KIND = 7;
    public const SEMITRAILER_ONBOARD_OPEN_KIND = 8;

    public const KINDS = [
        self::VAN_KIND => 'kind.van',
        self::ONBOARD_OPEN_KIND => 'kind.onboard_open',
        self::PICKUP_KIND => 'kind.pickup',
        self::ONBOARD_AWNING_KIND => 'kind.onboard_awning',
        self::TRAILER_ONBOARD_AWNING_KIND => 'kind.trailer_onboard_awning',
        self::TRACTOR_UNIT_KIND => 'kind.tractor_unit',
        self::SEMITRAILER_AWNING_KIND => 'kind.semitrailer_awning',
        self::SEMITRAILER_ONBOARD_OPEN_KIND => 'kind.semitrailer_onboard_open',
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
     * @ORM\Column(type="string", length=190, nullable=false)
     */
    private $marka;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=false)
     */
    private $model;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=false)
     */
    private $number;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true, "default":1})
     */
    private $kind;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=false, options={"default":0})
     */
    private $carrying = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarka(): ?string
    {
        return $this->marka;
    }

    public function setMarka(string $marka): self
    {
        $this->marka = $marka;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getKind(): ?int
    {
        return $this->kind;
    }

    public function setKind(int $kind): self
    {
        $this->kind = $kind;

        return $this;
    }

    public function getCarrying(): ?float
    {
        return $this->carrying;
    }

    public function setCarrying(float $carrying): self
    {
        $this->carrying = $carrying;

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
