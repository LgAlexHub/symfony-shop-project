<?php

namespace App\Controller\Admin\Api;

use App\Entity\Order;
use App\Entity\OrderProductRef;
use App\Repository\OrderRepository;
use App\Service\SessionTokenManager;
use App\Service\EnhancedEntityJsonSerializer;
use App\Controller\Admin\Api\ApiAdminController;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/admin/commandes', 'api.admin.orders.')]
/**
 * @author Name <alexlegras@hotmail.com>
 * @version 1.0.0
 * This controller handle admin api request
 */
class OrderController extends ApiAdminController
{
    #[Route('/debug', name: 'debug', env: 'dev')]
    public function debug(EntityManagerInterface $em, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer){
        $repo = $em->getRepository(Order::class);
        $test = $repo->main();
        
        dd($test);
    }

    #[Route('/', name: 'list')]
    /**
     * This method retreive a paginate and filter list of product in json format
     *
     * @param Request $request
     * @param SessionTokenManager $sessionTokenManager
     * @param EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer
     * @param OrderRepository $orderManager
     * @return void
     */
    public function listOrdersWithPaginationAndFilters(Request $request, SessionTokenManager $sessionTokenManager, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer, OrderRepository $orderManager){
        $this->checkBearerToken($request, $sessionTokenManager);


        $urlParams = $request->query->all();
        //Create orderBy array without preffix
        $ordersBy = array_reduce(array_keys($urlParams) , function ($carry, $urlParamName) use ($urlParams){
            if (strpos($urlParamName, "orderBy") === 0) {
                $carry[substr($urlParamName, strlen("orderBy"))] = $urlParams[$urlParamName];
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
            iterator_to_array($orders->results)
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
    /**
     * This method will try to set the property isDone of targeted order.
     * Will return a json response
     * @param Request $request
     * @param SessionTokenManager $sessionTokenManager
     * @param integer $id
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function updateIsDone(Request $request, SessionTokenManager $sessionTokenManager, int $id, EntityManagerInterface $em) : Response {
        $auth = $this->checkBearerToken($request, $sessionTokenManager);
        // Retrun json api error 401 if auth not valid
        if(!is_null($auth)){
            return $auth;
        }

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
    /**
     * This method retreive all product from a targeted order.
     * Note : nothing call this method, but i'll keept it :) 
     * @param Request $request
     * @param SessionTokenManager $sessionTokenManager
     * @param integer $id
     * @param EntityManagerInterface $em
     * @param EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer
     * @return Response
     */
    public function getOrderProduct(Request $request, SessionTokenManager $sessionTokenManager, int $id, EntityManagerInterface $em, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer) : Response {
        $auth = $this->checkBearerToken($request, $sessionTokenManager);
        // Retrun json api error 401 if auth not valid
        if(!is_null($auth)){
            return $auth;
        }

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

    #[Route('/infos', name: 'infos')]
    /**
     * Will return a json response with count of order group by their state
     *
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function infos(EntityManagerInterface $manager) : Response {
        return $this->json($manager->getRepository(Order::class)->countByState());
    }
}
