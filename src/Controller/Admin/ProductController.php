<?php

namespace App\Controller\Admin;

use App\Controller\Trait\ControllerToolsTrait;
use App\Entity\Product;
use App\Form\ProductType;
use App\Entity\ProductReference;
use App\Form\ProductReferenceType;

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

    #[Route('/', name: 'home')]
    /**
     * Handling home admin route, first route after login
     * render a list of all products
     *
     * @param EntityManagerInterface $manager
     * @return Response containt form view
     */
    public function index(EntityManagerInterface $manager): Response
    {
        $products = $manager->getRepository(Product::class)
            ->createQueryBuilder("product")
            ->orderBy("product.name", "ASC")
            ->getQuery()
            ->execute();
        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/new', name: 'new')]
    /**
     * This route render create product form, this route also handle the submit
     *
     * @param Request $request with form data
     * @param EntityManagerInterface $manager Will persist data in DB
     * @return Response either view or redirection
     */
    public function create(Request $request, EntityManagerInterface $manager) : Response {

        $productForm = $this->createForm(ProductType::class);

        $productForm->handleRequest($request);

        if($productForm->isSubmitted() && $productForm->isValid()){
            $newProduct = $productForm->getData();
            $manager->persist($newProduct);
            $manager->flush();
            $manager->detach($newProduct);
            return $this->redirectToRoute('admin.products.home');
        }

        return $this->render('admin/product/create.html.twig', [
            'form' => $productForm
        ]);
    }
   
    #[Route('/delete/{id}', name:'delete')]
    /**
     * Attempts to delete a product by its ID.
     *
     * Note:
     * - Redirects to the "admin.products.home" route if the deletion is successful.
     * - TODO: Add a 404 exception if the ID is not found.
     *
     * @param EntityManagerInterface $entityManager The entity manager handling the database delete.
     * @param int                    $id              The ID of the targeted product.
     *
     * @return Response Either a view or a redirection.
     *
     * @throws NotFoundHttpException If the targeted product is not found.
     */
    public function delete(EntityManagerInterface $entityManager, int $id) : Response {
        $productRepository = $entityManager->getRepository(Product::class);
        $product = $productRepository->find($id);
        $productsReferencesRepository = $entityManager->getRepository(ProductReference::class);
        $productsReferences = $productsReferencesRepository
            ->createQueryBuilder("proref")
            ->where("proref.product = :product_id")
            ->setParameter("product_id", $id)
            ->getQuery()
            ->execute();
        $this->checkEntityExistence($product, $id);
        foreach($productsReferences as $prodRef){
            $entityManager->remove($prodRef);
        }
        $entityManager->remove($product);
        $entityManager->flush();
        return $this->redirectToRoute("admin.products.home");
    }

    #[Route('/edit/{id}', name : 'edit')]
    /**
     * Displays and processes the edit form for a specified product.
     *
     * This route retrieves and renders the edit form for a specific product identified by its ID.
     *
     * @param Request                $request
     * @param int                    $id               The ID of the targeted product.
     * @param EntityManagerInterface $entityManager   The entity manager used to retrieve and persist data.
     *
     * @return Response Either the rendered form view or a redirection.
     *
     * @throws NotFoundHttpException If the targeted product is not found.
     */
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager) : Response {
        $product = $entityManager->getRepository(Product::class)
            ->find($id);
        $this->checkEntityExistence($product, $id);

        $productForm = $this->createForm(ProductType::class, $product);
        $productForm->handleRequest($request);
        if ($productForm->isSubmitted() && $productForm->isValid()){
            $updatedProduct = $productForm->getData();
            $product
                ->setName($updatedProduct->getName())
                ->setCategory($updatedProduct->getCategory());
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('admin.products.home');
        }
        return $this->render('admin/product/edit.html.twig', [
            'form' => $productForm
        ]);
    }

    #[Route('/{id}/prices', name : 'prices')]
    /**
     * Displays and processes the form for managing prices of a specified product.
     *
     * This route allows users to view and manage prices for a specific product identified by its ID.
     *
     * @param Request                $request
     * @param int                    $id              The ID of the targeted product.
     * @param EntityManagerInterface $manager         The entity manager used to retrieve and persist data.
     *
     * @return Response Either the rendered view or a redirection.
     *
     * @throws NotFoundHttpException If the targeted product is not found.
     */
    public function showPrices(Request $request, int $id, EntityManagerInterface $manager){
        $newRef = new ProductReference;
        $product = $manager->getRepository(Product::class)
            ->find($id);
        $this->checkEntityExistence($product, $id);
        $refForm = $this->createForm(ProductReferenceType::class, options: []);
        $refForm->handleRequest($request);
        if($refForm->isSubmitted() && $refForm->isValid()){
            $fomatedPrice = floatval(preg_replace('/\,/', '.', $request->get('product_reference')['price'] ?? 0)) * 100 ;
            $newRef = $refForm->getData();
            $newRef
                ->setProduct($product)
                ->setPrice($fomatedPrice);
            $manager->persist($newRef);
            $manager->flush();
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render('admin/product/reference/index.html.twig', [
                'product' => $product,
            'refForm' => $refForm
        ]);
    }

    #[Route('/{productId}/prices/{productRefId}', name : 'prices.delete')]
    /**
     * Deletes a product reference by its ID.
     *
     * This route allows users to delete a specific product reference identified by its ID.
     *
     * @param Request                $request
     * @param EntityManagerInterface $manager        The entity manager used to retrieve and persist data.
     * @param int                    $productRefId   The ID of the targeted product reference.
     *
     * @return Response A redirection to the previous page.
     *
     * @throws NotFoundHttpException If the targeted product reference is not found.
     */
    public function deletePrice(Request $request, EntityManagerInterface $manager, int $productRefId){
        $ref = $manager->getRepository(ProductReference::class)->find($productRefId);
        $this->checkEntityExistence($ref, $productRefId);
        $manager->remove($ref);
        $manager->flush();
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/{productId}/prices/{productRefId}/edit', name : 'prices.edit')]
    /**
     * Displays and processes the form for editing a product reference.
     *
     * This route allows users to edit the details of a specific product reference identified by its ID.
     *
     * @param Request                $request
     * @param EntityManagerInterface $manager        The entity manager used to retrieve and persist data.
     * @param int                    $productRefId   The ID of the targeted product reference.
     *
     * @return Response Either the rendered view or a redirection.
     *
     * @throws NotFoundHttpException If the targeted product reference is not found.
     */
    public function editPrice(Request $request, EntityManagerInterface $manager, int $productRefId){
        $ref = $manager->getRepository(ProductReference::class)->find($productRefId);
        $this->checkEntityExistence($ref, $productRefId);
        $productRefForm = $this->createForm(ProductReferenceType::class, $ref);
        $productRefForm->handleRequest($request);
        if ($productRefForm->isSubmitted() && $productRefForm->isValid()){
            $fomatedPrice = floatval(preg_replace('/\,/', '.', $request->get('product_reference')['price'] ?? 0)) * 100 ;
            $updatedProductRef = $productRefForm->getData();
            $ref->setWeight($updatedProductRef->getWeight())
                ->setWeightType($updatedProductRef->getWeightType())
                ->setPrice($fomatedPrice);
            $manager->persist($ref);
            $manager->flush();
            return $this->redirectToRoute('admin.products.prices', ['id' => $ref->getProduct()->getId()]);
        }

        return $this->render('admin/product/reference/edit.html.twig', [
            'refForm' => $productRefForm
        ]);
    }

}