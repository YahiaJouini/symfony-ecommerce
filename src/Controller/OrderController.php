<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\ProductRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    #[Route('/order/add/{id}', name: 'app_order_add', methods: ['POST'])]
    public function add(
        int $id,
        ProductRepository $productRepository,
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $product = $productRepository->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }
        
        $user = $this->getUser();
        
        $existingOrder = $orderRepository->findByUserAndProduct($user, $product);
        
        if ($existingOrder) {
            $this->addFlash('warning', 'Vous avez déjà commandé ce produit.');
            return $this->redirectToRoute('app_product_show', ['id' => $id]);
        }
        
         if ($product->getStock() <= 0) {
            $this->addFlash('error', 'Ce produit n\'est pas en stock.');
            return $this->redirectToRoute('app_product_show', ['id' => $id]);
        }
        
        $order = new Order();
        $order->setUser($user);
        $order->addProduct($product);
        $order->setOrderDate(new \DateTime());
        $order->setStatus('pending');
        
        $product->setStock($product->getStock() - 1);
        
        $entityManager->persist($order);
        $entityManager->flush();
        
        $this->addFlash('success', 'Produit commandé avec succès.');
        
        return $this->redirectToRoute('app_product_show', ['id' => $id]);
    }
    
    #[Route('/order/cancel/{id}', name: 'app_order_cancel', methods: ['POST'])]
    public function cancel(
        int $id,
        ProductRepository $productRepository,
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $product = $productRepository->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }
        
        $user = $this->getUser();
        
        $order = $orderRepository->findByUserAndProduct($user, $product);
        
        if (!$order) {
            $this->addFlash('warning', 'Aucune commande trouvée pour ce produit.');
            return $this->redirectToRoute('app_product_show', ['id' => $id]);
        }
        
        if (count($order->getProducts()) <= 1) {
            $entityManager->remove($order);
        } else {
            $order->removeProduct($product);
        }

        $product->setStock($product->getStock() + 1);
        
        $entityManager->flush();
        
        $this->addFlash('success', 'Commande annulée avec succès.');
        
        return $this->redirectToRoute('app_product_show', ['id' => $id]);
    }
}