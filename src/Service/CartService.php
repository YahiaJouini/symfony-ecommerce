<?php

namespace App\Service;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private $session;

    public function __construct(private RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function addToCart(int $id): void
    {
        $cart = $this->session->get('cart', []);
        
        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->session->set('cart', $cart);
    }

    public function removeFromCart(int $id): void
    {
        $cart = $this->session->get('cart', []);
        
        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }

        $this->session->set('cart', $cart);
    }

    public function decreaseQuantity(int $id): void
    {
        $cart = $this->session->get('cart', []);
        
        if (!empty($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }

        $this->session->set('cart', $cart);
    }

    public function getCart(): array
    {
        return $this->session->get('cart', []);
    }

    public function clearCart(): void
    {
        $this->session->remove('cart');
    }

    public function getTotal(array $products): float
    {
        $total = 0;
        $cart = $this->getCart();

        foreach ($cart as $id => $quantity) {
            $product = $products[$id] ?? null;
            if ($product) {
                $total += $product->getPrice() * $quantity;
            }
        }

        return $total;
    }
} 