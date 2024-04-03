<?php

namespace App\Controller\Admin\Api;

use App\Entity\Order;
use App\Entity\OrderProductRef;
use App\Repository\OrderRepository;
use App\Service\SessionTokenManager;
use App\Service\EnhancedEntityJsonSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/admin/commandes', 'api.admin.orders.')]
class OrderController extends AbstractController
{
    #[Route('/debug', name: 'debug', env: 'dev')]
    public function debug(EntityManagerInterface $em, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer){
        $repo = $em->getRepository(OrderProductRef::class);
        $orderProductRefs = $repo->findProductWithRelatedInOrder(443);
        $enhancedEntityJsonSerializer
        ->setObjectToSerialize($orderProductRefs)
        ->setOptions([AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn (object $opr, string $format, array $context) => $opr->getId()])
        ->setAttributes([
            'quantity',
            'item' => [
                'price', 'weight', 'weightType', 'slug', 'imageUrl', 'product' => [
                    'name', 'description', 'slug'
                ]
            ]
        ]);
        dd($enhancedEntityJsonSerializer->serialize());
    }

    private function checkBearerToken(Request $request, SessionTokenManager $sessionTokenManager){
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
    }

    #[Route('/', name: 'list')]
    public function listOrdersWithPaginationAndFilters(Request $request, SessionTokenManager $sessionTokenManager, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer, OrderRepository $orderManager){
        $this->checkBearerToken($request, $sessionTokenManager);

        //Remove orderBy prefix from url parameters
        $urlParams = $request->query->all();
        $ordersBy = array_reduce(array_keys($urlParams) , function ($carry, $urlParamName) use ($urlParams){
            if (strpos($urlParamName, "orderBy") === 0) {
                $camelCaseKeyWithUnderscore = preg_replace('/([a-z])([A-Z])/', '$1_$2', substr($urlParamName, strlen("orderBy")));
                $snake_case_key = strtolower($camelCaseKeyWithUnderscore);
                
                $carry[$snake_case_key] = $urlParams[$urlParamName];
            }
            return $carry;
        }, []); 
        $page = $request->get('page') ?? 1;
        $query = $request->get('query') ?? '';
        $orders = $orderManager->paginateFilteredOrders(page: $page, userSearchQuery: $query, orderBy: $ordersBy);
        $orders->results = array_map(
            function($item){
                $tmpItem = $item[0];
                $tmpItem->totalPrice = $item["totalOrderAmount"];
                return $tmpItem;
            },
            $orders->results
        );

        $enhancedEntityJsonSerializer
            ->setObjectToSerialize($orders->results)
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
                'createdAt',
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

    #[Route('/{id}/validee', name: 'done')]
    public function updateIsDone(Request $request, SessionTokenManager $sessionTokenManager, int $id, EntityManagerInterface $em){
        $this->checkBearerToken($request, $sessionTokenManager);
        if (is_null($id))
            return $this->json(['error' => ['msg' => sprintf("Id manquant dans l'url")]], 422);

        $targetedOrder = $em->getRepository(Order::class)->findOneBy(['id' => $id]);

        if (is_null($targetedOrder))
            return  $this->json(['error' => ['msg' => sprintf("Commande avec l'id %d inexistant", $id)]], 404);
       
        $targetedOrder->setIsDone(!$targetedOrder->getIsDone());
        $em->persist($targetedOrder);
        $em->flush();
        return $this->json("Ok", status: 200);
    }


    #[Route('/{id}/produits', name:'products')]
    public function getOrderProduct(Request $request, SessionTokenManager $sessionTokenManager, int $id, EntityManagerInterface $em, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer){
        $this->checkBearerToken($request, $sessionTokenManager);
        if (is_null($id))
        return $this->json(['error' => ['msg' => sprintf("Id manquant dans l'url")]], 422);

        $targetedOrder = $em->getRepository(Order::class)->findOneBy(['id' => $id]);

        if (is_null($targetedOrder))
            return  $this->json(['error' => ['msg' => sprintf("Commande avec l'id %d inexistant", $id)]], 404);

        $orderProductRefs = $em->getRepository(OrderProductRef::class)->findProductWithRelatedInOrder($id);
        $enhancedEntityJsonSerializer
            ->setObjectToSerialize($orderProductRefs)
            ->setOptions([AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn (object $opr, string $format, array $context) => $opr->getId()])
            ->setAttributes([
                'quantity',
                'item' => [
                    'price', 'weight', 'weightType', 'slug', 'imageUrl', 'product' => [
                        'name', 'description', 'slug'
                    ]
                ]
            ]);
        return new Response($enhancedEntityJsonSerializer->serialize(), headers: [
            'Content-Type' => 'application/json'
        ]);
    }
}
