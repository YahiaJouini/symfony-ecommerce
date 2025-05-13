<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $orderDate = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $total = 0;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(mappedBy: 'orderRef', targetEntity: OrderItem::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $orderItems;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getOrderDate(): ?\DateTimeInterface
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTimeInterface $orderDate): static
    {
        $this->orderDate = $orderDate;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrderRef($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrderRef() === $this) {
                $orderItem->setOrderRef(null);
            }
        }

        return $this;
    }
    
    /**
     * Get all products in this order
     * 
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        $products = new ArrayCollection();
        foreach ($this->orderItems as $orderItem) {
            $products->add($orderItem->getProduct());
        }
        return $products;
    }

    public function containsProduct(Product $product): bool
    {
        foreach ($this->orderItems as $orderItem) {
            if ($orderItem->getProduct() === $product) {
                return true;
            }
        }
        return false;
    }
    
    public function getOrderItemForProduct(Product $product): ?OrderItem
    {
        foreach ($this->orderItems as $orderItem) {
            if ($orderItem->getProduct() === $product) {
                return $orderItem;
            }
        }
        return null;
    }

    public function getTotalQuantity(): int
    {
        $total = 0;
        foreach ($this->orderItems as $orderItem) {
            $total += $orderItem->getQuantity();
        }
        return $total;
    }

}