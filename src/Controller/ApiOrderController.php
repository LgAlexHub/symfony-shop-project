<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProductRef;
use App\Entity\ProductReference;
use App\Service\EnhancedEntityJsonSerializer;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductReferenceRepository;
use App\Controller\Trait\ControllerToolsTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;


#[Route('api', 'api.')]
/**
 * @author Aléki <alexlegras@hotmail.com>
 * @version 1
 * Controller that handle api endpoint for order  routes
 */
class ApiOrderController extends AbstractController
{
    use ControllerToolsTrait;

    #[Route('/produits/recherche', name: 'query')]
    /**
     * API Endpoint: get product refrences by name.
     *
     * @param ProductReferenceRepository $productReferenceRepository
     * @param Request $request
     * @param EnhancedEnityJsonSerializer $enhancedEnityJsonSerializer
     * @return Response
     */
    public function searchProductReferencesByQueryApi(ProductReferenceRepository $productReferenceRepository, Request $request, EnhancedEntityJsonSerializer $enhancedEnityJsonSerializer): Response
    {
        $queryString = $request->get('nom');
        if (empty($queryString) || is_null($queryString))
            return new Response(null, 401, ['Content-Type' => 'application/json']);
        $productReferences = $productReferenceRepository->refByQueryWithRelated($queryString);
        $enhancedEnityJsonSerializer
            ->setObjectToSerialize($productReferences)
            ->setOptions([AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn (object $orderProductRef, string $format, array $context) => $orderProductRef->getId()])
            ->setAttributes([
                'price', 'weight', 'weightType', 'slug', 'imageUrl', 'product' => [
                    'name', 'description', 'slug'
                ]
            ]);
        return new Response($enhancedEnityJsonSerializer->serialize(), headers: [
            'Content-Type' => 'application/json'
        ]);
    }

    #[Route('/commandes/{uuidOrder}/ajouter-produit', name: 'add-product-to-basket', methods: ['POST'])]
    /**
     * API Endpoint: Try to link an item to targeted order or if it exist will increase the quantity.
     *
     * @param Request $request
     * @param string $uuidOrder
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function addProductToBasket(Request $request, string $uuidOrder, EntityManagerInterface $manager): Response
    {
        $orderAndProductRef = $this->checkOrderAndProductRefExistance($request, $uuidOrder, $manager);
        if(isset($orderAndProductRef->error))
            return $this->json(["Erreur" => $orderAndProductRef->error['msg']], status: $orderAndProductRef->error['code']);
        // We try to look items already in order to see if we just need to increase quantity or if we need to link it to current order
        $orderProductRef = $manager->getRepository(OrderProductRef::class)->findProductInOrder($orderAndProductRef->order->getId(), $orderAndProductRef->productRef->getId());
        match (is_null($orderProductRef)) {
            true =>  $orderProductRef = (new OrderProductRef())
                ->setOrder($orderAndProductRef->order)
                ->setItem($orderAndProductRef->productRef)
                ->setQuantity(1),
            false => $orderProductRef->setQuantity($orderProductRef->getQuantity() + 1)
        };

        $manager->persist($orderProductRef);
        $manager->flush();
        $manager->detach($orderProductRef);

        return $this->json('OK', status: 200);
    }

    #[Route('/commandes/{uuidOrder}/diminuer-produit', name: 'decrease-product-from-basket', methods: ['POST'])]
    /**
     * Undocumented function
     *
     * @return Response
     */
    public function decreaseProductFromBasket(Request $request, string $uuidOrder, EntityManagerInterface $manager): Response
    {
        $orderAndProductRef = $this->checkOrderAndProductRefExistance($request, $uuidOrder, $manager);
        if(isset($orderAndProductRef->error))
            return $this->json(["Erreur" => $orderAndProductRef->error['msg']], status: $orderAndProductRef->error['code']);
        // We try to look items already in order to see if we just need to increase quantity or if we need to link it to current order
        $orderProductRef = $manager->getRepository(OrderProductRef::class)->findProductInOrder($orderAndProductRef->order->getId(), $orderAndProductRef->productRef->getId());
        if(is_null($orderProductRef))
            return $this->json(['erreur' => 'Le produit dans votre panier que vous recherchez n\'existe pas'], 404);
        match ($orderProductRef->getQuantity() < 2) {
            true =>  $manager->remove($orderProductRef),
            false => $orderProductRef->setQuantity($orderProductRef->getQuantity() - 1)
        };
        $manager->flush();
        return $this->json("OK", 200);
    }

    #[Route('/commandes/{uuidOrder}/supprimer-produit', name: 'decrease-product-from-basket', methods: ['POST'])]
    /**
     * Undocumented function
     *
     * @param Request $request
     * @param string $uuidOrder
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function deleteProductFromBasket(Request $request, string $uuidOrder, EntityManagerInterface $manager) : Response {
        $orderAndProductRef = $this->checkOrderAndProductRefExistance($request, $uuidOrder, $manager);
        if(isset($orderAndProductRef->error))
            return $this->json(["Erreur" => $orderAndProductRef->error['msg']], status: $orderAndProductRef->error['code']);
        $orderProductRef = $manager->getRepository(OrderProductRef::class)->findProductInOrder($orderAndProductRef->order->getId(), $orderAndProductRef->productRef->getId());
        if(is_null($orderProductRef))
            return $this->json(['erreur' => 'Le produit dans votre panier que vous recherchez n\'existe pas'], 404);
        $manager->remove($orderProductRef);
        $manager->flush();
        return $this->json("OK", 200);

    }

    /**
     * Check if targeted productRef belongs to targeted order
     *
     * @param Request $request
     * @param string $uuidOrder
     * @param EntityManagerInterface $manager
     * @return object
     */
    private function checkOrderAndProductRefExistance(Request $request, string $uuidOrder, EntityManagerInterface $manager) : object
    {
        if (empty($uuidOrder) || is_null($uuidOrder) || !Uuid::isValid($uuidOrder))
            return  (object)['error' => ['code' => 422, 'msg' =>'UUID absent ou erroné']];
        $order = $manager->getRepository(Order::class)->findOneBy(['uuid' => $uuidOrder]);
        $orderItems = new ArrayCollection($manager->getRepository(OrderProductRef::class)->findBy([
            'order' => $order->getId()
        ]));
        $order->setItems($orderItems);
        if (is_null($order))
            return  (object)['error' => ['code' => 404, 'msg' => sprintf("Commande avec l'UUID %s inexistante", $uuidOrder)]];
        
        $productRefSlugForm = $this->createFormBuilder(null, ['csrf_protection' => false])
            ->add('productReferenceSlug', Type\TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->getForm();
        if (!$this->handleAndCheckForm($request, $productRefSlugForm))
            return  (object)['error' => ['code' => 422, 'msg' => "Formulaire incomplent, slug manquant"]];
        
        $productRef = $manager->getRepository(ProductReference::class)->findBySlug($productRefSlugForm->getData()["productReferenceSlug"]);
        if (is_null($productRef))
            return  (object)['error' => ['code' => 404, 'msg' => sprintf("Référence de produit avec le slug %s inexistant", $productRefSlugForm->getData()["productReferenceSlug"])]];
        
        return (object)[
            'order' => $order,
            'productRef' => $productRef
        ];
    }
    
    #[Route('/commandes', name: 'orders.all', methods: ['GET'], env: 'dev')]
    /**
     * Dev env method , to dump all orders available in db
     * @param EntityManagerInterface $manager
     * @param EnhancedEntityJsonSerializer $enhancedEnityJsonSerializer
     * @return Response
     */
    public function dumpAllOrder(EntityManagerInterface $manager, EnhancedEntityJsonSerializer $enhancedEnityJsonSerializer) : Response {
        $orders = $manager->getRepository(Order::class)->findAllRelated();
        $enhancedEnityJsonSerializer
            ->setObjectToSerialize($orders)
            ->setOptions([AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn (object $orderProductRef, string $format, array $context) => $orderProductRef->getId()])
            ->setAttributes([
                'serializeUuid',
                'clientFirstName',
                'clientLastName',
                'email',
                'comment',
                'isValid',
                'items' => [
                    'quantity',
                    'item' => [
                        'price', 'weight', 'weightType', 'slug', 'imageUrl', 'product' => [
                            'name', 'description', 'slug'
                        ]
                    ]
                ]
            ]);
        return new Response($enhancedEnityJsonSerializer->serialize(), headers: [
            'Content-Type' => 'application/json'
        ]);
    }


}
