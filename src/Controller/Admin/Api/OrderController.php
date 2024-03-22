<?php

namespace App\Controller\Admin\Api;

use App\Repository\OrderRepository;
use App\Service\EnhancedEntityJsonSerializer;
use App\Service\SessionTokenManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/admin/commandes', 'api.admin.orders.')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function listOrdersWithPaginationAndFilters(Request $request, SessionTokenManager $sessionTokenManager, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer, OrderRepository $orderManager){
        //Check if bearer token is in request header
        $bearerToken = $request->headers->get('authorization');
        if (is_null($bearerToken)){
            return $this->json("Unauthorized", 401);
        }
        //Check if bearer token is same as the one in the session
        $bearerToken = explode(" ", $bearerToken)[1];
        if ($sessionTokenManager->getApiToken() !== $bearerToken){
            return $this->json("Unauthorized", 401);
        }
        $orders = $orderManager->orderPagination();
        $enhancedEntityJsonSerializer
            ->setObjectToSerialize($orders->paginator)
            ->setOptions([AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn (object $order, string $format, array $context) => $order->getId()])
            ->setAttributes([
                'id',
                'serializeUuid',
                'clientFirstName',
                'clientLastName',
                'email',
                'comment',
                'isValid',
                'isDone',
                'totalPrice',
                'items' => [
                    'quantity',
                    'item' => [
                        'price', 'weight', 'weightType', 'slug', 'imageUrl', 'product' => [
                            'name', 'description', 'slug'
                        ]
                    ]
                ]
            ]);
        return new Response(
            sprintf("{\n\t\"orders\": %s,
                \"totalPage\": %d,
                \"nbResult\": %d,
                \"page\": %d
            }", $enhancedEntityJsonSerializer->serialize(), $orders->maxPage, $orders->nbResult, $orders->page), 
            headers: [ 'Content-Type' => 'application/json']
        );
    }
}
