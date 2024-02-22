<?php

namespace App\Controller;

use App\Controller\Trait\ControllerToolsTrait;

use App\Entity\Order;
use App\Form\OrderType;
use App\Entity\OrderProductRef;
use App\Entity\ProductReference;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('commandes', 'orders.')]
class OrderController extends AbstractController
{

    use ControllerToolsTrait;

    #[Route('/commander', name: 'order')]
    public function askOrder(Request $request, EntityManagerInterface $manager): Response
    {
        $templateVariables = ["form" => $this->createForm(OrderType::class)];
        $productRefSlugFromURL = $request->get('produit');
        $productRefManager = $manager->getRepository(ProductReference::class);

        // a non valid slug will pass through this test but will not be add to order 
        if (!is_null($productRefSlugFromURL) && !empty($productRefSlugFromURL))
            $templateVariables["preSelectedOrderItemJson"] = $productRefSlugFromURL;
        //     $jsonSerialiser = new Serializer(
        //         [new GetSetMethodNormalizer()],
        //         ['json' => new JsonEncoder()]
        //     );
        //     $templateVariables["preSelectedOrderItemJson"] = $jsonSerialiser->serialize($productRef, 'json', [
        //         'circular_reference_handler' => fn ($object) => $object->getId(),
        //         AbstractNormalizer::ATTRIBUTES => ['price', 'slug', 'weight', 'weightType', 'product' => ['name']]
        //     ]);
        // }


        if ($this->handleAndCheckForm($request, $templateVariables['form'])) {
            $newOrder = $templateVariables['form']->getData();
            // dd($templateVariables['form']->get('items')->getData());
            $newOrder
                ->setIsValid(false)
                ->setUuid(Uuid::v4());
            $manager->persist($newOrder);
            $manager->flush();
            if (($productRef = $productRefManager->findBySlug($templateVariables['form']->get('items')->getData()))) {
                $newOrderProductRef = new OrderProductRef;
                $newOrderProductRef
                    ->setOrder($newOrder)
                    ->setItem($productRef)
                    ->setQuantity(1);
                $manager->persist($newOrderProductRef);
                $manager->flush();
                $manager->detach($newOrderProductRef);
            }
            $manager->detach($newOrder);
            return $this->redirectToRoute("orders.choose-products", ['commande' => urlencode($newOrder->getUuid())]);
            // $newOrder = $orderForm->getData();
            // $serializedData = $orderForm->get('items')->getData();
            // dd($request->request->all());           //TODO : Création de l'entité en db , création order + orderItems 
            // TODO ++ : Déclencer un envoi de mail + notification B-O
        }

        return $this->render('order/form.html.twig', $templateVariables);
    }

    #[Route('/{commande}/choisir-ses-produits', name: 'choose-products')]
    public function askOrderProductsView(string $commande, EntityManagerInterface $manager)
    {
        $order = $manager->getRepository(Order::class)->findByUuidWithRelated($commande);

        if (is_null($order)) {
            throw $this->createNotFoundException("No entity found for uuid : $commande");
        }

        $jsonSerialiser = new Serializer(
            [new ObjectNormalizer()],
            ['json' => new JsonEncoder()]
        );
        $orderItemsJsonSerialized = $jsonSerialiser->serialize($order->getItems(), 'json', [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn (object $orderProductRef, string $format, array $context) => $orderProductRef->getId(),
            AbstractNormalizer::ATTRIBUTES => [
                'quantity',
                // 'order' => ['clientFirstName', 'clientLastName', 'email', 'comment', 'serializeUuid'],
                'item'  => [
                    'price', 'weight', 'weightType', 'slug', 'imageUrl', 'product' => [
                        'name', 'description', 'slug'
                    ]
                ],
            ]
        ]);
        // dd($order->getItems()[0]->getItem()->getImageUrl());
        return $this->render("order/products.html.twig", [
            "orderUuid" => $order->getUuid(),
            "orderItems" => $orderItemsJsonSerialized
        ]);
    }

}
