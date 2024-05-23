<?php

namespace App\Controller\Admin\Api;

use App\Entity\ProductReference;
use App\Form\ProductReferenceType;
use App\Service\SessionTokenManager;
use App\Service\EnhancedEntityJsonSerializer;
use App\Controller\Trait\ControllerToolsTrait;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/references', 'api.admin.product_references.')]
class ProductReferenceController extends ApiAdminController
{
    use ControllerToolsTrait;

    #[Route('/{id}', name: 'edit', methods:['PATCH'])]
    /**
     * This method will try to edit a product reference with content of request
     *
     * @param Request $request
     * @param SessionTokenManager $sessionTokenManager
     * @param EntityManagerInterface $em
     * @param EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer
     * @param integer $id
     * @return Response
     */
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
        return  $this->json(['error' => ['msg' => $productReferenceForm->getErrors(true)]], 422);
    }

    #[Route('/{id}', name: 'delete', methods:['DELETE'])]
    /**
     * This method cannot work currently because every ref are bind to orders 
     * TODO : Use a soft delete
     *
     * @param Request $request
     * @param SessionTokenManager $sessionTokenManager
     * @param EntityManagerInterface $em
     * @param integer $id
     * @return void
     */
    public function delete(Request $request, SessionTokenManager $sessionTokenManager, EntityManagerInterface $em, int $id){
        $authCheck = $this->checkBearerToken($request, $sessionTokenManager);
        if(!is_null($authCheck)){
            return $authCheck;
        }

        if (is_null($id))
            return $this->json(['error' => ['msg' => sprintf("Id manquant dans l'url")]], 422);

        $targetedProductReference = $em->getRepository(ProductReference::class)->findOneBy(['id' => $id]);

        if (is_null($targetedProductReference))
            return  $this->json(['error' => ['msg' => sprintf("Référence avec l'id %d inexistant", $id)]], 404);

        try {
            $em->remove($targetedProductReference);
            $em->flush();
        } catch (\Throwable $th) {
            // dd($th->getMessage()); //TODO ecrire les erreur en français
            return $this->json(['error' => ['msg' => 'Une erreur est survenue pendant la suppression dans la base de données']], 500);
        }

        return $this->json('OK');
    }
}
