<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function products(Request $request, EntityManagerInterface $manager){
        $page = $request->get('page') ?? 1;
        $category = $request->get('categorie') !== null ? urldecode($request->get('categorie')) :  null;
        $paginatedProduct = $manager->getRepository(Product::class)->productOrderByNamePaginate($page, category : $category);
        $productCategories = $manager->getRepository(ProductCategory::class)->findCategoryWithProduct();
        return $this->render('home/products.html.twig', [
            'selectedCategory' => $category,
            'categories' => $productCategories,
            'totalPage' => $paginatedProduct->maxPage,
            'page' => $paginatedProduct->page,
            'products' => $paginatedProduct->productPaginator
        ]);
        // dd($product, ($page - 1) * $perPage);    
    }

}
