<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/dashboard')]
class DashboardOrderController extends AbstractController
{
    #[Route('/orders', name: 'admin_orders')]
    public function index(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findAll();
        
        return $this->render('dashboard/order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/orders/{id}', name: 'admin_order_show')]
    public function show(Order $order): Response
    {
        return $this->render('dashboard/order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/orders/{id}/update-status', name: 'admin_order_update_status', methods: ['POST'])]
    public function updateStatus(
        Request $request,
        Order $order,
        EntityManagerInterface $entityManager
    ): Response
    {
        $status = $request->request->get('status');
        
        if (in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            $order->setStatus($status);
            $entityManager->flush();
            
            $this->addFlash('success', 'Statut de la commande mis à jour avec succès.');
        } else {
            $this->addFlash('error', 'Statut de commande non valide.');
        }
        
        return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
    }
}