<?php

namespace App\Controller;

use App\Entity\Product;

use Doctrine\ORM\EntityManagerInterface;

use App\Controller\Trait\ControllerToolsTrait;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('produits', 'products.')]
class ProductController extends AbstractController
{
    use ControllerToolsTrait;

    #[Route('/{slug}', name: 'view')]
    public function viewPage(EntityManagerInterface $manager, string $slug): Response
    {
        $product = $manager->getRepository(Product::class)->findBySlug($slug);
        $this->checkEntityExistence($product, "slug" ,$slug);
        return $this->render('product/view.html.twig', [
            'product' => $product
        ]);
    }
}
