<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/nos-produits', name:'home.products')]
    public function products(EntityManagerInterface $manager){
        $products = $manager->getRepository(Product::class)
            ->createQueryBuilder("product")
            ->getQuery()
            ->execute();
        return $this->render('home/products.html.twig', [
            'products' => $products
        ]);
    }

}
