<?php

namespace App\Controller\Api;

use App\Entity\Order;
use App\Entity\OrderProductRef;
use App\Entity\ProductReference;
use App\Service\EnhancedEntityJsonSerializer;

use Doctrine\ORM\EntityManagerInterface;
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


#[Route('api/commandes', 'api.orders.')]
/**
 * @author Aléki <alexlegras@hotmail.com>
 * @version 1
 * Controller that handle api endpoint for order  routes
 */
class OrderController extends AbstractController
{
    use ControllerToolsTrait;
    
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
