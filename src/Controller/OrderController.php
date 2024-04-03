<?php

namespace App\Controller;

use App\Controller\Trait\ControllerToolsTrait;

use App\Entity\Order;
use App\Form\OrderType;
use App\Entity\OrderProductRef;
use App\Entity\ProductReference;
use App\Service\EnhancedEntityJsonSerializer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('commandes', 'orders.')]
class OrderController extends AbstractController
{

    use ControllerToolsTrait;

    #[Route('/commander', name: 'order')]
    /**
     * This method handle route /commander which is a form to order an item from shop
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function askOrder(Request $request, EntityManagerInterface $manager): Response
    {
        $templateVariables = ["form" => $this->createForm(OrderType::class)];
        $productRefSlugFromURL = $request->get('produit');
        $productRefManager = $manager->getRepository(ProductReference::class);

        // a non valid slug will pass through this test but will not be add to order 
        if (!is_null($productRefSlugFromURL) && !empty($productRefSlugFromURL))
            $templateVariables["preSelectedOrderItemJson"] = $productRefSlugFromURL;

        if ($this->handleAndCheckForm($request, $templateVariables['form'])) {
            $newOrder = $templateVariables['form']->getData();
            // dd($templateVariables['form']->get('items')->getData());
            $newOrder
                ->setIsValid(false)
                ->setIsDone(false)
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
            return $this->redirectToRoute("orders.choose-products", ['uuid' => urlencode($newOrder->getUuid())]);
            // TODO ++ : Déclencer un envoi de mail + notification B-O
        }

        return $this->render('order/form.html.twig', $templateVariables);
    }

    #[Route('/{uuid}/choisir-ses-produits', name: 'choose-products')]
    /**
     * This method handle route /uuid/choisir-ses-produits which is a form to add or remove products from ur order
     *
     * @param string $uuid
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function askOrderProductsView(string $uuid, EntityManagerInterface $manager, EnhancedEntityJsonSerializer $enhancedEnityJsonSerializer): Response
    {
        $order = $manager->getRepository(Order::class)->findOneBy(['uuid' => $uuid]);

        if (is_null($order))
            throw $this->createNotFoundException("No entity found for uuid : $uuid");

        if($order->getIsValid())
            // Ajouter un feedback, on ne peut éditer une commande déjà confirmer
            return new Response(null, 500);

        $orderItems = new ArrayCollection($manager->getRepository(OrderProductRef::class)->findBy([
            'order' => $order->getId()
        ]));

        $order->setItems($orderItems);

        $enhancedEnityJsonSerializer
            ->setObjectToSerialize($order->getItems())
            ->setOptions([AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn (object $orderProductRef, string $format, array $context) => $orderProductRef->getId()])
            ->setAttributes([
                'quantity',
                'item' =>
                ['price', 'weight', 'weightType', 'slug', 'imageUrl', 'product' => [
                    'name', 'description', 'slug'
                ]]
            ]);

        return $this->render("order/products.html.twig", [
            "orderUuid" => $order->getUuid(),
            "orderItems" => $enhancedEnityJsonSerializer->serialize()
        ]);
    }

    #[Route('/{uuid}/valider', name: 'order-confirm')]
    /**
     * This method handle route handle /uuid/valider which is the last step to order
     *
     * @param Request $request
     * @param string $uuid
     * @param EntityManagerInterface $manager
     * @return null|Response
     */
    public function confirmOrder(Request $request, string $uuid, EntityManagerInterface $manager) : null|Response {
        if (empty($uuid) || is_null($uuid) || !Uuid::isValid($uuid)){
            // TODO : Feedback utilisateur commande inexistante
            return null;
        }
        
        $orderWithProducts = $manager->getRepository(Order::class)->findOneBy(['uuid' => $uuid]);
        if($orderWithProducts->getItems()->count() < 1){
            // TODO : Feedback utilisateur on ne peut pas commander sans produit
            return null;
        }
        
        $orderWithProducts->setIsValid(true);
        $manager->persist($orderWithProducts);
        $manager->flush();
        $manager->detach($orderWithProducts);

        // TODO : redirection avec feedback, voir pour faire une page récap commande , voir pdf avec 
        return $this->redirectToRoute("home");
    }
}
