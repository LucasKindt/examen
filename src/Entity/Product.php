<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(length: 1000)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    /**
     * @var Collection<int, ProductCategory>
     */
    #[ORM\ManyToMany(targetEntity: ProductCategory::class, mappedBy: 'products')]
    private Collection $productCategories;

    /**
     * @var Collection<int, OrderProduct>
     */
    #[ORM\OneToMany(targetEntity: OrderProduct::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $OrderProducts;

    public function __construct()
    {
        $this->productCategories = new ArrayCollection();
        $this->OrderProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    public function getProductCategories(): Collection
    {
        return $this->productCategories;
    }

    public function addProductCategory(ProductCategory $productCategory): static
    {
        if (!$this->productCategories->contains($productCategory)) {
            $this->productCategories->add($productCategory);
            $productCategory->addProduct($this);
        }

        return $this;
    }

    public function removeProductCategory(ProductCategory $productCategory): static
    {
        if ($this->productCategories->removeElement($productCategory)) {
            $productCategory->removeProduct($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return Collection<int, OrderProduct>
     */
    public function getOrderProducts(): Collection
    {
        return $this->OrderProducts;
    }

    public function addOrderProduct(OrderProduct $orderProduct): static
    {
        if (!$this->OrderProducts->contains($orderProduct)) {
            $this->OrderProducts->add($orderProduct);
            $orderProduct->setProduct($this);
        }

        return $this;
    }

    public function removeOrderProduct(OrderProduct $orderProduct): static
    {
        if ($this->OrderProducts->removeElement($orderProduct)) {
            // set the owning side to null (unless already changed)
            if ($orderProduct->getProduct() === $this) {
                $orderProduct->setProduct(null);
            }
        }

        return $this;
    }
}
