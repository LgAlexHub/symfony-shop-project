<?php

namespace App\Controller\Admin;

use App\Entity\ProductCategory;
use App\Form\ProductCategoryType;
use App\Controller\Trait\ControllerToolsTrait;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/categories', 'admin.categories.')]
/**
 * Controller's routes are prefixed with /admin/categories, and name are prefixed with admin.categories.
 * This controller handle product category back office, allow admins to modify product categories content as their like.
 * @author Aléki <alexlegras@hotmail.com>
 * @version 1
 */
class ProductCategoryController extends AbstractController
{
    use ControllerToolsTrait;

    private static string $templatePath = "admin/product_category";
    private static string $homeRoute = "admin.categories.index";

    #[Route('/', name: 'index')]
    /**
     * Display all product category in database
     *
     * @return Response
     */
    public function index(EntityManagerInterface $manager): Response
    {
        $categories = $manager->getRepository(ProductCategory::class)
            ->createQueryBuilder("cat")
            ->getQuery()
            ->execute();
        return $this->render(self::$templatePath."/index.html.twig", [
            'categories' => $categories
        ]);
    }

    #[Route('/add', name: 'add')]
    /**
     * Try to add a new product category in database, will render view with form if it's incorrect
     * and will redirect to product categories home route if it's successfully inserted
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function add(Request $request, EntityManagerInterface $manager): Response {
        $categoryForm = $this->createForm(ProductCategoryType::class);
        $categoryForm->handleRequest($request);
        if ($this->handleAndCheckForm($request, $categoryForm)){
            $newCategory = $categoryForm->getData();
            $manager->persist($newCategory);
            $manager->flush();
            return $this->redirectToRoute(self::$homeRoute);
        }

        return $this->renderWithRefererUrl($request, self::$templatePath."/form.html.twig",[
            "form" => $categoryForm
        ]);
    }

    #[Route('/{slug}/delete', name: 'delete')]
    /**  
     * Try to delete a  product category in database, will throw exception if it's incorrect
     * and will redirect to product categories home route if it's successfully deleted.
     * @param EntityManagerInterface $manager
     * @param string $slug
     * @throws createNotFoundException
     * @return void
     */
    public function delete(EntityManagerInterface $manager, string $slug) : Response{
        $category = $manager->getRepository(ProductCategory::class)->findBySlug($slug);
        $this->checkEntityExistence($category, "slug", $slug);
        $manager->remove($category);
        $manager->flush();
        return $this->redirectToRoute(self::$homeRoute);
    }

    #[Route('/{slug}/edit', name :'edit')]
    /**
     * This method will render a pre-filled form with targeted slug, also handle the submit
     * Either render the form with error or redirect to home route.
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param string $slug
     * @throws createNotFoundException if product category's slug not found
     * @return Response
     */
    public function edit(Request $request, EntityManagerInterface $manager, string $slug) : Response {
        $category = $manager->getRepository(ProductCategory::class)->findBySlug($slug);
        $this->checkEntityExistence($category, "slug", $slug);
        $categoryForm = $this->createForm(ProductCategoryType::class, $category);
        $categoryForm->handleRequest($request);
        if ($this->handleAndCheckForm($request, $categoryForm)){
            $newCategory = $categoryForm->getData();
            $category->setLabel($newCategory->getLabel());
            $manager->persist($category);
            $manager->flush();
            return $this->redirectToRoute(self::$homeRoute);
        }
        return $this->renderWithNavigation($category, self::$templatePath."/form.html.twig",[
            "form" => $categoryForm
        ]);
    }
}
