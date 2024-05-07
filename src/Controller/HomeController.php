<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Service\EnhancedEntityJsonSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

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
        $favoriteProducts = $manager->getRepository(Product::class)
            ->createQueryBuilder("product")
            ->innerJoin("product.productReferences", "prodRef")
            ->where("product.isFavorite = 1")
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();
        return $this->render('home/index.html.twig', [
            'products' => $favoriteProducts
        ]);
    }

    /**
     * Render view with all product from DB
     *
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/nos-produits', name:'home.products')]
    public function products(Request $request, EntityManagerInterface $manager, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer) : Response{
        
        return $this->render('home/products.html.twig');
        // dd($product, ($page - 1) * $perPage);    
    }

}
