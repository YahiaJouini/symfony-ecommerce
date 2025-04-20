<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository
    )
    {
        $homeProducts = $productRepository->findBy(['showHome' => true]);
        $categories = $categoryRepository->findAll();

        return $this->render('home/index.html.twig', [
            'homeProducts' => $homeProducts,
            'categories'=>$categories
        ]);
    }
}
?>