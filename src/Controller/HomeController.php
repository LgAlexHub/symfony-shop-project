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
    public function index(EntityManagerInterface $manager): Response
    {
        $products = $manager->getRepository(Product::class)->getSelectionProductWithoutSoftDelete();
        return $this->render('home/index.html.twig', [
            'selection' => $products
        ]);
    }

    /**
     * Render view with all product from DB
     *
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/nos-produits', name:'home.products')]
    public function products() : Response{
        return $this->render('home/products.html.twig');
    }

    #[Route('/politique-confidentialite', name: 'home.privacy')]
    public function privacyPolicy(){
        return $this->render('home/privacy.html.twig');
    }

}
