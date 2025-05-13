<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CartService;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private ProductRepository $productRepository,
        private OrderService $orderService
    ) {
    }

    #[Route('/cart', name: 'app_cart')]
    public function index(): Response
    {
        $cart = $this->cartService->getCart();
        $products = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);
            if ($product) {
                $products[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
                $total += $product->getPrice() * $quantity;
            }
        }

        return $this->render('cart/index.html.twig', [
            'items' => $products,
            'total' => $total,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(int $id): Response
    {
        $this->cartService->addToCart($id);
        $this->addFlash('success', 'Product added to cart successfully!');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(int $id): Response
    {
        $this->cartService->removeFromCart($id);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/decrease/{id}', name: 'cart_decrease')]
    public function decrease(int $id): Response
    {
        $this->cartService->decreaseQuantity($id);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/clear', name: 'cart_clear')]
    public function clear(): Response
    {
        $this->cartService->clearCart();
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/validate', name: 'cart_validate')]
    public function validateCart(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $order = $this->orderService->createOrderFromCart();
        
        if (!$order) {
            $this->addFlash('error', 'Unable to create order. Please check your cart.');
            return $this->redirectToRoute('app_cart');
        }

        $this->addFlash('success', 'Your order has been placed successfully!');
        return $this->redirectToRoute('app_user_orders');
    }
} 