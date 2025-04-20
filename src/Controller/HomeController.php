<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController{
    #[Route('/',name:'idk')]
    public function index(){
        return $this->render('home/index.html.twig',['name'=>"idk"]) ;
    }
}
?>