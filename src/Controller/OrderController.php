<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\ProductRepository;
use App\Repository\OrderRepository;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    #[Route('/orders', name: 'app_user_orders')]
    public function index(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $orders = $orderRepository->findBy(['user' => $user, 'status' => ['completed', 'processing', 'shipped']], ['orderDate' => 'DESC']);
        
        // Get pending order if it exists
        $pendingOrder = $orderRepository->findOneBy(['user' => $user, 'status' => 'pending']);
        
        return $this->render('order/index.html.twig', [
            'orders' => $orders,
            'pendingOrder' => $pendingOrder,
        ]);
    }
    
    #[Route('/order/{id}', name: 'app_order_show')]
    public function show(Order $order): Response
    {
        if ($order->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir cette commande.');
        }
    
        return $this->render('order/show.html.twig', [
            'order' => $order,
            'orderItems' => $order->getOrderItems(),
        ]);
    }
    

    #[Route('/order/add/{id}', name: 'app_order_add')]
    public function add(
        int $id,
        Request $request,
        ProductRepository $productRepository,
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $product = $productRepository->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }
        
        $quantity = max(1, (int)$request->request->get('quantity', 1));
        
        if ($product->getStock() < $quantity) {
            $this->addFlash('error', 'Stock insuffisant. Seulement ' . $product->getStock() . ' disponible(s).');
            return $this->redirectToRoute('app_product_show', ['id' => $id]);
        }
        
        $user = $this->getUser();
        
        $order = $orderRepository->findOrCreatePendingOrder($user);
        
        $orderItem = $order->getOrderItemForProduct($product);
        
        if ($orderItem) {
            $newQuantity = $orderItem->getQuantity() + $quantity;
            
            if ($product->getStock() < $newQuantity - $orderItem->getQuantity()) {
                $this->addFlash('error', 'Stock insuffisant pour ajouter ' . $quantity . ' de plus.');
                return $this->redirectToRoute('app_product_show', ['id' => $id]);
            }
            
            $orderItem->setQuantity($newQuantity);
        } else {
            $orderItem = new OrderItem();
            $orderItem->setOrderRef($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            
            $order->addOrderItem($orderItem);
        }
        
        $product->setStock($product->getStock() - $quantity);
        
        if (!$order->getId()) {
            $entityManager->persist($order);
        }
        
        $entityManager->flush();
        
        $this->addFlash('success', $quantity . ' ' . $product->getName() . ' ajouté(s) à votre commande.');
        
        return $this->redirectToRoute('app_product_show', ['id' => $id]);
    }
    
    #[Route('/order/update-quantity/{orderItemId}', name: 'app_order_update_quantity', methods: ['POST'])]
    public function updateQuantity(
        int $orderItemId,
        Request $request,
        OrderItemRepository $orderItemRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $orderItem = $orderItemRepository->find($orderItemId);
        
        if (!$orderItem) {
            throw $this->createNotFoundException('Item de commande non trouvé.');
        }
        
        if ($orderItem->getOrderRef()->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cette commande.');
        }
        
        $quantity = max(1, (int)$request->request->get('quantity', 1));
        $product = $orderItem->getProduct();
        $oldQuantity = $orderItem->getQuantity();
        
        $availableStock = $product->getStock() + $oldQuantity;
        
        if ($quantity > $availableStock) {
            $this->addFlash('error', 'Stock insuffisant. Maximum disponible: ' . $availableStock);
            return $this->redirectToRoute('app_order_show', ['id' => $orderItem->getOrderRef()->getId()]);
        }
        
        $stockDifference = $oldQuantity - $quantity;
        $product->setStock($product->getStock() + $stockDifference);
        
        $orderItem->setQuantity($quantity);
        
        $entityManager->flush();
        
        $this->addFlash('success', 'Quantité mise à jour.');
        
        return $this->redirectToRoute('app_order_show', ['id' => $orderItem->getOrderRef()->getId()]);
    }
    
    #[Route('/order/remove-item/{orderItemId}', name: 'app_order_remove_item', methods: ['POST'])]
    public function removeItem(
        int $orderItemId,
        OrderItemRepository $orderItemRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $orderItem = $orderItemRepository->find($orderItemId);
        
        if (!$orderItem) {
            throw $this->createNotFoundException('Item de commande non trouvé.');
        }
        
        if ($orderItem->getOrderRef()->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cette commande.');
        }
        
        $order = $orderItem->getOrderRef();
        $product = $orderItem->getProduct();
        
        $product->setStock($product->getStock() + $orderItem->getQuantity());
        
        $order->removeOrderItem($orderItem);
        $entityManager->remove($orderItem);
        
        if ($order->getOrderItems()->isEmpty()) {
            $entityManager->remove($order);
        }
        
        $entityManager->flush();
        
        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    
    #[Route('/order/checkout/{id}', name: 'app_order_checkout', methods: ['POST'])]
    public function checkout(
        Order $order,
        EntityManagerInterface $entityManager
    ): Response {
        if ($order->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à finaliser cette commande.');
        }
        
        if ($order->getStatus() !== 'pending') {
            $this->addFlash('warning', 'Cette commande a déjà été traitée.');
            return $this->redirectToRoute('app_user_orders');
        }
        
        $order->setStatus('processing');
        $entityManager->flush();
        
        $this->addFlash('success', 'Votre commande a été confirmée et est en cours de traitement.');
        
        return $this->redirectToRoute('app_user_orders');
    }
}