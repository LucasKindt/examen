<?php

namespace App\Entity;

use App\Repository\OrderProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderProductRepository::class)]
class OrderProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\ManyToOne(inversedBy: 'orderProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $TOrder = null;

    #[ORM\Column(nullable: true)]
    private ?bool $stockUpdated = false;

    #[ORM\ManyToOne(inversedBy: 'OrderProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getTOrder(): ?Order
    {
        return $this->TOrder;
    }

    public function setTOrder(?Order $TOrder): static
    {
        $this->TOrder = $TOrder;

        return $this;
    }

    public function __toString(): string {
        return $this->getProduct()->getName();
    }

    public function isStockUpdated(): ?bool
    {
        return $this->stockUpdated;
    }

    public function setStockUpdated(?bool $stockUpdated): static
    {
        $this->stockUpdated = $stockUpdated;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
