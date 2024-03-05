<?php

namespace App\Controller\Admin;

use App\Controller\Trait\ControllerToolsTrait;
use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/commandes', 'admin.orders.')]
class OrderController extends AbstractController
{
    use ControllerToolsTrait;
    // On peut ici , voir une commandes , voir la liste des commandes 
    private static string $templatePath = "admin/order";
    private static string $homeRoute = "admin.orders.index";

    #[Route('/', name: 'index')]
    /**
     * Handling home admin route, first route after login
     * render a list of all products
     *
     * @param OrderRepository $manager
     * @return Response containt form view
     */
    public function index(OrderRepository $manager): Response
    {
        $orders = $manager->findBy([], array('createdAt' => 'ASC'));
        
        return $this->render(self::$templatePath.'/index.html.twig', [
            'orders' => $orders,
        ]);
    }
   
    // #[Route('/{slug}/delete', name:'delete')]
    // /**
    //  * Attempts to delete a product by its slug.
    //  *
    //  * Note:
    //  * - Redirects to the "admin.products.home" route if the deletion is successful.
    //  * - TODO: Add a 404 exception if the slug is not found.
    //  *
    //  * @param EntityManagerInterface $entityManager The entity manager handling the database delete.
    //  * @param string                    $slug              The slug of the targeted product.
    //  *
    //  * @return Response Either a view or a redirection.
    //  *
    //  * @throws NotFoundHttpException If the targeted product is not found.
    //  */
    // public function delete(EntityManagerInterface $manager, string $slug) : Response {
    //     $product = $manager->getRepository(Product::class)->findBySlug($slug);
    //     $this->checkEntityExistence($product, "slug", $slug);
    //     foreach($product->getProductReferences() as $ref){
    //         $manager->remove($ref);
    //     }
    //     $manager->remove($product);
    //     $manager->flush();
    //     return $this->redirectToRoute("admin.products.index");
    // }

    // #[Route('/{slug}/edit', name : 'edit')]
    // /**
    //  * Displays and processes the edit form for a specified product.
    //  *
    //  * This route retrieves and renders the edit form for a specific product identified by its slug.
    //  *
    //  * @param Request                $request
    //  * @param string                 $slug               The slug of the targeted product.
    //  * @param EntityManagerInterface $entityManager   The entity manager used to retrieve and persist data.
    //  *
    //  * @return Response Either the rendered form view or a redirection.
    //  *
    //  * @throws NotFoundHttpException If the targeted product is not found.
    //  */
    // public function edit(Request $request, string $slug, EntityManagerInterface $entityManager) : Response {
    //     $product = $entityManager->getRepository(Product::class)->findBySlug($slug);
    //     $this->checkEntityExistence($product,"slug" ,$slug);
    //     $productForm = $this->createForm(ProductType::class, $product);
    //     if ($this->handleAndCheckForm($request, $productForm)){
    //         $updatedProduct = $productForm->getData();
    //         $product
    //             ->setDescription($updatedProduct->getDescription())
    //             ->setName($updatedProduct->getName())
    //             ->setCategory($updatedProduct->getCategory());
    //         $entityManager->persist($product);
    //         $entityManager->flush();
    //         return $this->redirectToRoute(self::$homeRoute);
    //     }
    //     return $this->renderWithNavigation($product, self::$templatePath.'/form.html.twig', [
    //         'form' => $productForm
    //     ]);
    // }
}
