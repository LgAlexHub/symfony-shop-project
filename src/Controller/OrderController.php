<?php

namespace App\Controller;

use App\Controller\Trait\ControllerToolsTrait;

use App\Entity\Order;
use App\Form\OrderType;
use App\Entity\OrderProductRef;
use App\Entity\ProductReference;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;



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
        $form = $this->createForm(OrderType::class);
        if ($this->handleAndCheckForm($request, $form)) {
            $newOrder = $form->getData();
            $manager->persist($newOrder);
            $parsedBasket = json_decode($request->get('basket') ?? []);
            $referencesFromDatabase = $manager->getRepository(ProductReference::class)
                ->findBy(['id' => array_map(
                    fn($item) => $item?->reference?->id,
                    $parsedBasket
                )]);
            foreach ($referencesFromDatabase as $index => $ref) {
                $newOrderRef = new OrderProductRef;
                $newOrderRef->setOrder($newOrder);
                $newOrderRef->setItem($ref);
                $newOrderRef->setQuantity($parsedBasket[$index]->quantity);
                $manager->persist($newOrderRef);
            }
            $manager->flush();
            return $this->redirectToRoute("orders.confirm", ['uuid' => $newOrder->getUuid()]);
            //TODO : redirect to page de confirmation
        }

        return $this->render('order/form.html.twig', [
            "form"         => $form,
            "basketItems"  => $request->get('basket_items') ?? json_encode([]),
        ]);
    }

    #[Route('/{uuid}/validee', name: 'confirm')]
    /**
     * Undocumented function
     *
     * @param Request $request
     * @param Order $order
     * @param TransportInterface $mailer
     * @return Response
     */
    public function confirmOrder(Request $request, String $uuid, TransportInterface $mailer, EntityManagerInterface $manager) : Response {
        $order = $manager->getRepository(Order::class)->findOneBy(['uuid' => $uuid]);
        $email = new TemplatedEmail;
        $email->to($order->getEmail());
        $email->subject("Gout'mé cha - Votre commande a été transmise");
        $email->htmlTemplate("emails/orderConfirmed.html.twig");
        $email->context(['order' => $order]);
        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $th) {
            die($th);
        }
        return $this->render("order/confirm.html.twig", ["order" => $order]);
    }
}
