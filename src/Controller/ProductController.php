<?php

namespace App\Controller;

use App\Controller\Trait\ControllerToolsTrait;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('produits', 'products.')]
class ProductController extends AbstractController
{
    use ControllerToolsTrait;

    #[Route('/{slug}', name: 'view')]
    public function viewPage(EntityManagerInterface $manager, string $slug): Response
    {
        $product = $manager->getRepository(Product::class)->findBySlug($slug);
        $this->checkEntityExistence($product, $slug);
        return $this->render('product/view.html.twig', [
            'product' => $product
        ]);
    }
}
