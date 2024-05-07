<?php

namespace App\Controller\Admin;

use App\Controller\Trait\ControllerToolsTrait;
use App\Entity\Product;
use App\Entity\ProductReference;
use App\Form\ProductReferenceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admin/products/{slug}/references', 'admin.products.references.')]
/**
 * Controller's routes are prefixed with /admin/products, and name are prefixed with admin.products.
 * This controller handle product back office, allow admins to modify products content as their like.
 * @author Aléki <alexlegras@hotmail.com>
 * @version 1
 */
class ProductReferenceController extends AbstractController
{

    use ControllerToolsTrait;

    private static string $templatePath = "admin/product_reference";

    #[Route('/', name: 'index')]
    /**
     * Undocumented function
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param string $slug
     * @throws NotFoundHttpException If the targeted product is not found.
     * @return Response
     */
    public function index(Request $request, EntityManagerInterface $manager, string $slug): Response
    {
        $product = $manager->getRepository(Product::class)->findBySlug($slug);
        $this->checkEntityExistence($product, "slug", $slug);
        $productRefFrom = $this->createForm(ProductReferenceType::class);
        if ($this->handleAndCheckForm($request, $productRefFrom)) {
            $fomatedPrice = floatval(preg_replace('/\,/', '.', $request->get('product_reference')['price'] ?? 0)) * 100;
            $newProductReference = $productRefFrom->getData();
            $newProductReference
                ->setProduct($product)
                ->setPrice($fomatedPrice);
            $manager->persist($newProductReference);
            $manager->flush();
            return $this->redirect($request->headers->get('referer'));
        }
        return $this->renderWithNavigation($product, self::$templatePath.'/index.html.twig' , [
            'form' => $productRefFrom,
            'product' => $product
        ]);
    }

    #[Route('/{refSlug}/delete', name: 'delete')]
    /**
     * Undocumented function
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param string $refSlug
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $manager, string $refSlug): Response
    {
        $productRef = $manager->getRepository(ProductReference::class)->findBySlug($refSlug);
        $this->checkEntityExistence($productRef, "slug", $refSlug);
        $manager->remove($productRef);
        $manager->flush();
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/{refSlug}/edit', name: 'edit')]
    /**
     * Displays and processes the form for editing a product reference.
     *
     * This route allows users to edit the details of a specific product reference identified by its ID.
     *
     * @param Request                $request
     * @param EntityManagerInterface $manager        The entity manager used to retrieve and persist data.
     * @param string                $refSlug   The slug of the targeted product reference.
     *
     * @return Response Either the rendered view or a redirection.
     *
     * @throws NotFoundHttpException If the targeted product reference is not found.
     */
    public function edit(Request $request, EntityManagerInterface $manager, string $refSlug)
    {
        $ref = $manager->getRepository(ProductReference::class)->findBySlug($refSlug);
        $this->checkEntityExistence($ref, "slug", $refSlug);
        $productRefForm = $this->createForm(ProductReferenceType::class, $ref);
        if ($this->handleAndCheckForm($request, $productRefForm)) {
            $fomatedPrice = floatval(preg_replace('/\,/', '.', $request->get('product_reference')['price'] ?? 0)) * 100;
            $updatedProductRef = $productRefForm->getData();
            $ref->setWeight($updatedProductRef->getWeight())
                ->setWeightType($updatedProductRef->getWeightType())
                ->setPrice($fomatedPrice);
            $manager->persist($ref);
            $manager->flush();
            return $this->redirectToRoute('admin.products.references.index', ['id' => $ref->getProduct()->getId()]);
        }

        return $this->renderWithNavigation($ref, self::$templatePath . '/edit.html.twig', [
            'form' => $productRefForm
        ]);
    }
}
