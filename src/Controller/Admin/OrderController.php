<?php

namespace App\Controller\Admin;

use App\Controller\Trait\ControllerToolsTrait;
use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\SessionTokenManager;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(Request $request, OrderRepository $manager, SessionTokenManager $sessionTokenManager): Response
    {

        $orders = $manager->orderPagination();
        $ordersWithTotalPrices = [];
        foreach($orders->paginator as $order){
            $ordersWithTotalPrices[] = [
                'order' => $order,
                'totalPrice' => $order->getTotalPrice()
            ];
        }
        $renderArray =  [
            'orders'  => $ordersWithTotalPrices,
            'totalPage' => $orders->maxPage,
            'nbResult'  => $orders->nbResult,
            'page'      => $orders->page,
            'api_token' => $sessionTokenManager->getApiToken()
        ];

        return $this->render(self::$templatePath.'/index.html.twig', $renderArray);
    }

    #[Route('/{id}', name: 'show')]
    public function show(OrderRepository $manager, int $id) : Response {
        $order = $manager->findOneBy(['id' => $id]);
        dd($order);
        $total = $order->getItems()->reduce(fn(int $accumulator, object $orderItem) : int => $accumulator + ($orderItem->getQuantity() * $orderItem->getItem()->getPrice()), 0);
        if (is_null($order))
            return new Response(null, 404);

        return $this->render(self::$templatePath.'/show.html.twig', [
            'order' => $order,
            'orderTotalPrice' => $total
        ]);
    }

    #[Route('/{uuid}/valider', name: 'validate')]
    public function changeIsValidState(EntityManagerInterface $manager, string $uuid) : Response {
        $order = $manager->getRepository(Order::class)->findByUuid($uuid);
        if (is_null($order))
            return new Response(null, 404);
        $order->setIsDone(!$order->getIsDone());
        $manager->persist($order);
        $manager->flush();
        $manager->detach($order);
        return $this->redirectToRoute(self::$homeRoute);
    }
}
