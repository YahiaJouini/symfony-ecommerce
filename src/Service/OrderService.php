<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class OrderService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CartService $cartService,
        private ProductRepository $productRepository,
        private Security $security
    ) {
    }

    public function createOrderFromCart(): ?Order
    {
        $cart = $this->cartService->getCart();
        if (empty($cart)) {
            return null;
        }

        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user) {
            return null;
        }

        $order = new Order();
        $order->setUser($user);
        $order->setStatus('pending');
        $order->setOrderDate(new \DateTime());
        
        $total = 0;
        foreach ($cart as $productId => $quantity) {
            $product = $this->productRepository->find($productId);
            if (!$product || $product->getStock() < $quantity) {
                continue;
            }

            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            $orderItem->setPrice($product->getPrice());
            $orderItem->setOrderRef($order);
            
            $total += $product->getPrice() * $quantity;
            
            $product->setStock($product->getStock() - $quantity);
            
            $this->entityManager->persist($orderItem);
        }

        $order->setTotal($total);
        
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        
        $this->cartService->clearCart();
        
        return $order;
    }
} 