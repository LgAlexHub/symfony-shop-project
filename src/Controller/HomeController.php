<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @author Aléki <alexlegras@hotmail.com>
 * @version 1
 * Controller that handle home routes
 */
class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    /**
     * Render home view to client
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * Render view with all product from DB
     *
     * @param EntityManagerInterface $manager
     * @return void
     */
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
