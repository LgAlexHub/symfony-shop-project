<?php

namespace App\Controller\Admin;

use App\Controller\Trait\ControllerToolsTrait;
use App\Entity\Product;
use App\Form\ProductType;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/products', 'admin.products.')]
/**
 * Controller's routes are prefixed with /admin/products, and name are prefixed with admin.products.
 * This controller handle product back office, allow admins to modify products content as their like.
 * @author Aléki <alexlegras@hotmail.com>
 * @version 1
 */
class ProductController extends AbstractController
{
    use ControllerToolsTrait;

    private static string $templatePath = "admin/product";
    private static string $homeRoute = "admin.products.index";

    #[Route('/', name: 'index')]
    /**
     * Handling home admin route, first route after login
     * render a list of all products
     *
     * @param EntityManagerInterface $manager
     * @return Response containt form view
     */
    public function index(Request $request, EntityManagerInterface $manager): Response
    {
        // dd($request->get('query'));
        $page = $request->get('page') ?? 1;
        $query = $request->get('activeQuery') ?? null;
        $newQuery = $request->get('query') ?? null;
        // dd(['new' => $newQuery, 'old' => $query]);
        if (!is_null($newQuery))
            $query = $newQuery;
        $paginatedProduct = $manager
            ->getRepository(Product::class)
            ->productOrderByNamePaginate($page, category : null, query : $query);

        $renderArray =  [
            'products'  => $paginatedProduct->productPaginator,
            'totalPage' => $paginatedProduct->maxPage,
            'nbResult'  => $paginatedProduct->nbResult,
            'page'      => $paginatedProduct->page,
        ];
        
        if (!is_null($query)){
            $renderArray['query'] = $query; 
        }

        return $this->render(self::$templatePath.'/index.html.twig', $renderArray);
    }

    #[Route('/add', name: 'add')]
    /**
     * This route render create product form, this route also handle the submit
     *
     * @param Request $request with form data
     * @param EntityManagerInterface $manager Will persist data in DB
     * @return Response either view or redirection
     */
    public function add(Request $request, EntityManagerInterface $manager) : Response {
        $productForm = $this->createForm(ProductType::class);
        if($this->handleAndCheckForm($request, $productForm)){
            $newProduct = $productForm->getData();
            $manager->persist($newProduct);
            $manager->flush();
            $manager->detach($newProduct);
            return $this->redirectToRoute(self::$homeRoute);
        }
        return $this->renderWithRefererUrl($request, self::$templatePath.'/form.html.twig', [
            'form' => $productForm
        ]);
    }
   
    #[Route('/{slug}/delete', name:'delete')]
    /**
     * Attempts to delete a product by its slug.
     *
     * Note:
     * - Redirects to the "admin.products.home" route if the deletion is successful.
     * - TODO: Add a 404 exception if the slug is not found.
     *
     * @param EntityManagerInterface $entityManager The entity manager handling the database delete.
     * @param string                    $slug              The slug of the targeted product.
     *
     * @return Response Either a view or a redirection.
     *
     * @throws NotFoundHttpException If the targeted product is not found.
     */
    public function delete(EntityManagerInterface $manager, string $slug) : Response {
        $product = $manager->getRepository(Product::class)->findBySlug($slug);
        $this->checkEntityExistence($product, "slug", $slug);
        foreach($product->getProductReferences() as $ref){
            $manager->remove($ref);
        }
        $manager->remove($product);
        $manager->flush();
        return $this->redirectToRoute("admin.products.index");
    }

    #[Route('/{slug}/edit', name : 'edit')]
    /**
     * Displays and processes the edit form for a specified product.
     *
     * This route retrieves and renders the edit form for a specific product identified by its slug.
     *
     * @param Request                $request
     * @param string                 $slug               The slug of the targeted product.
     * @param EntityManagerInterface $entityManager   The entity manager used to retrieve and persist data.
     *
     * @return Response Either the rendered form view or a redirection.
     *
     * @throws NotFoundHttpException If the targeted product is not found.
     */
    public function edit(Request $request, string $slug, EntityManagerInterface $entityManager) : Response {
        $product = $entityManager->getRepository(Product::class)->findBySlug($slug);
        $this->checkEntityExistence($product,"slug" ,$slug);
        $productForm = $this->createForm(ProductType::class, $product);
        if ($this->handleAndCheckForm($request, $productForm)){
            $updatedProduct = $productForm->getData();
            $product
                ->setDescription($updatedProduct->getDescription())
                ->setName($updatedProduct->getName())
                ->setCategory($updatedProduct->getCategory());
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute(self::$homeRoute);
        }
        return $this->renderWithNavigation($product, self::$templatePath.'/form.html.twig', [
            'form' => $productForm
        ]);
    }
}