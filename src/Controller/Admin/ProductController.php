<?php

namespace App\Controller\Admin;

use App\Controller\Trait\ControllerToolsTrait;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductCategoryRepository;
use App\Service\EnhancedEntityJsonSerializer;
use App\Service\SessionTokenManager;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

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
    public function index(SessionTokenManager $sessionTokenManager, ProductCategoryRepository $productCategoryRepository, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer): Response
    {
        $enhancedEntityJsonSerializer->setObjectToSerialize($productCategoryRepository->findAll())
            ->setOptions([AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn (object $category, string $format, array $context) => $category->getId()])
            ->setAttributes([
                'id', 'label'
            ]);
        return $this->render(self::$templatePath.'/index.html.twig', [
            'api_token'       => $sessionTokenManager->getApiToken(),
            'jsonCategories'  => $enhancedEntityJsonSerializer->serialize()
        ]);
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
     * - Do not work because product are bind to ref that are bind to orders
     * - Need to create soft delete
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
}