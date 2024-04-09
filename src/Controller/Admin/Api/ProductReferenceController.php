<?php

namespace App\Controller\Admin\Api;

use App\Controller\Trait\ControllerToolsTrait;
use App\Entity\ProductReference;
use App\Form\ProductReferenceType;
use App\Service\EnhancedEntityJsonSerializer;
use App\Service\SessionTokenManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/references', 'api.admin.product_references.')]
class ProductReferenceController extends ApiAdminController
{
    use ControllerToolsTrait;

    #[Route('/{id}', name: 'edit', methods:['PATCH'])]
    public function edit(Request $request, SessionTokenManager $sessionTokenManager, EntityManagerInterface $em, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer, int $id): Response
    {
        $authCheck = $this->checkBearerToken($request, $sessionTokenManager);
        if(!is_null($authCheck)){
            return $authCheck;
        }

        if (is_null($id))
            return $this->json(['error' => ['msg' => sprintf("Id manquant dans l'url")]], 422);

        $targetedProductReference = $em->getRepository(ProductReference::class)->findOneBy(['id' => $id]);

        if (is_null($targetedProductReference))
            return  $this->json(['error' => ['msg' => sprintf("Référence avec l'id %d inexistant", $id)]], 404);


        $enhancedEntityJsonSerializer->setObjectToSerialize($targetedProductReference)
            ->setAttributes([
                'id',
                'formatedPrice',
                'slug',
                'weight',
                'weightType',
                'imageUrl'
            ]);
        $productReferenceForm = $this->createForm(ProductReferenceType::class, options: ['csrf_protection' => false]);
        $productReferenceForm->submit(json_decode($request->getContent(), true));

       
        if($productReferenceForm->isValid()){
            $editedProductReference = $productReferenceForm->getData();
            $targetedProductReference->setPrice($editedProductReference->getPrice())
                        ->setWeight($editedProductReference->getWeight())
                ->setWeightType($editedProductReference->getWeightType());
            $em->persist($targetedProductReference);
            $em->flush();
            $em->detach($targetedProductReference);
            $enhancedEntityJsonSerializer->setObjectToSerialize($targetedProductReference)
                ->setAttributes([
                    'id',
                    'formatedPrice',
                    'slug',
                    'weight',
                    'weightType',
                    'imageUrl'
                ]);
            return $this->apiJson($enhancedEntityJsonSerializer->serialize());
        }
        //TODO : Faire un vrai feedback via l'api
        return $this->json([
            'error' => ['msg' => sprintf("Erreur de formulaire")]
        ]);
    }
}
