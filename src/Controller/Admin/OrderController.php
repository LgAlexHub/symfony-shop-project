<?php

namespace App\Controller\Admin;

use App\Service\SessionTokenManager;
use App\Controller\Trait\ControllerToolsTrait;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/commandes', 'admin.orders.')]
class OrderController extends AbstractController
{
    use ControllerToolsTrait;

    private static string $templatePath = "admin/order";
    private static string $homeRoute = "admin.orders.index";

    #[Route('/', name: 'index')]
    /**
     * Handling home admin route
     * render a view containing a vue component which render all orders
     *
     * @param OrderRepository $manager
     * @return Response containt form view
     */
    public function index(SessionTokenManager $sessionTokenManager): Response
    {
        return $this->render(self::$templatePath.'/index.html.twig', [
            'api_token' => $sessionTokenManager->getApiToken()
        ]);
    }
}
