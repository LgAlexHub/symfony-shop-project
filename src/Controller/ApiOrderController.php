<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProductRef;
use App\Entity\ProductReference;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductReferenceRepository;
use App\Controller\Trait\ControllerToolsTrait;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


#[Route('api', 'api.')]
class ApiOrderController extends AbstractController
{
    use ControllerToolsTrait;

    #[Route('/api/order', name: 'app_api_order')]
    public function index(): Response
    {
        return $this->render('api_order/index.html.twig', [
            'controller_name' => 'ApiOrderController',
        ]);
    }

    #[Route('/produits/recherche', name: 'query')]
    public function queryProductApiEndPoint(ProductReferenceRepository $manager, Request $request): Response
    {
        $queryString = $request->get('nom');
        $products = [];

        if (empty($queryString) || is_null($queryString))
            return new Response(null, 401, ['Content-Type' => 'application/json']);


        $products = $manager->refByQueryWithRelated($queryString);
        $jsonSerialiser = new Serializer(
            [new ObjectNormalizer()],
            ['json' => new JsonEncoder()]
        );
        $orderItemsJsonSerialized = $jsonSerialiser->serialize($products, 'json', [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn (object $orderProductRef, string $format, array $context) => $orderProductRef->getId(),
            AbstractNormalizer::ATTRIBUTES => [
                'price', 'weight', 'weightType', 'slug', 'imageUrl', 'product' => [
                    'name', 'description', 'slug'
                ]
            ]
        ]);
        // dd($orderItemsJsonSerialized);
        return new Response($orderItemsJsonSerialized, headers: [
            'Content-Type' => 'application/json'
        ]);
    }

    #[Route('/commandes/{uuidOrder}/ajouter-produit', name: 'add-product-to-basket', methods: ['POST'])]
    public function addProductToBasket(Request $request, string $uuidOrder, EntityManagerInterface $manager)
    {
        if (empty($uuidOrder) || is_null($uuidOrder) || !Uuid::isValid($uuidOrder))
            return $this->json(['Erreur : commande '],status: 404);

        $form = $this->createFormBuilder(null, ['csrf_protection' => false])
            ->add('productReferenceSlug', Type\TextType::class, ['constraints' => [
                new NotBlank(),
            ]])
            ->getForm();
        
        if (!$this->handleAndCheckForm($request, $form)) 
            return new Response('forbiden', 401, ['Content-Type' => 'application/json']);

        $data = $form->getData();
        $productRef = $manager->getRepository(ProductReference::class)->findBySlug($data["productReferenceSlug"]);
        $order = $manager->getRepository(Order::class)->findByUuidWithRelated($uuidOrder);
        $this->checkEntityExistence($order, "uuid" ,$uuidOrder);
        $orderProductRef = $manager->getRepository(OrderProductRef::class)->findProductInOrder($order->getId(), $productRef->getId());

        if (is_null($productRef))
            return new Response('forbiden', 401, ['Content-Type' => 'application/json']);
    

        match(is_null($orderProductRef)){
            true =>  $orderProductRef = (new OrderProductRef())
                ->setOrder($order)
                ->setItem($productRef)
                ->setQuantity(1),
            false => $orderProductRef->setQuantity($orderProductRef->getQuantity() + 1)
        };
        
        $manager->persist($orderProductRef);
        $manager->flush();
        $manager->detach($orderProductRef);

        return new Response('ok', 200, ['Content-Type' => 'application/json']);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function removeProductFromBasket(){

    }
}
