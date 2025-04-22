<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;

final class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(
        ProductRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        $query = $repository->createQueryBuilder('p')->getQuery();
        
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );
        
        return $this->render('product/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}