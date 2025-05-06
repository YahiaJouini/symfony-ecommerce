<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(
        ProductRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $searchTerm = $request->query->get('search', '');
        $categoryId = $request->query->get('category');
        
        $categoryId = $categoryId !== null ? (int)$categoryId : null;
        
        $query = $repository->searchProducts($searchTerm, $categoryId);
        
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );
        
        return $this->render('product/index.html.twig', [
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
            'categoryId' => $categoryId,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(
        int $id,
        ProductRepository $productRepository,
        OrderRepository $orderRepository
    ): Response {
        $product = $productRepository->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }
        
        $isOrdered = false;
        $orderItem = null;
        $currentUser = $this->getUser();
        
        if ($currentUser) {
            $userOrders = $orderRepository->findBy(['user' => $currentUser]);
            
            foreach ($userOrders as $order) {
                if ($order->containsProduct($product)) {
                    $isOrdered = true;
                    $orderItem = $order->getOrderItemForProduct($product);
                    break;
                }
            }
        }
        
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'isOrdered' => $isOrdered,
            'orderItem' => $orderItem,
        ]);
    }

    #[Route('/product/category/{categoryId}', name: 'app_product_by_category')]
    public function productsByCategory(
        int $categoryId,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $category = $categoryRepository->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée.');
        }

        $searchTerm = $request->query->get('search', '');
        $queryBuilder = $productRepository->searchProducts($searchTerm, $categoryId);

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            3
        );

        return $this->render('product/category.html.twig', [
            'pagination' => $pagination,
            'category' => $category,
            'searchTerm' => $searchTerm,
        ]);
    }
}