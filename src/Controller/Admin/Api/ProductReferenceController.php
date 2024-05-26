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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/admin/references', 'api.admin.product_references.')]
class ProductReferenceController extends ApiAdminController
{
    use ControllerToolsTrait;

    #[Route('/{id}', name: 'edit', methods:['POST'])]
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
        $productReferenceForm->submit($request->request->all());
        if($productReferenceForm->isValid()){
            $formImage = $request->files->get('imageFile');
            if(!is_null($formImage)){
                if($formImage->getError() !== 0){
                    $error = match ($formImage->getError()) {
                        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Le fichier téléchargé dépasse la taille maximale autorisée.',
                        UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement téléchargé.',
                        UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été téléchargé.',
                        UPLOAD_ERR_NO_TMP_DIR => 'Un dossier temporaire est manquant.',
                        UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque.',
                        UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté le téléchargement du fichier.',
                        default => 'Une erreur inconnue est survenue lors de l\'upload.',
                    };
                    return  $this->json(['error' => ['msg' => [['message' => $error]]]], 422);
                }
                $allowedImageTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/bmp',
                    'image/webp',
                    'image/tiff',
                    'image/svg+xml',
                    'image/x-icon',
                    'image/heif',
                    'image/heic'
                ];
                if(!in_array($formImage->getMimeType(), $allowedImageTypes)){
                    return $this->makeCustomJsonErrorAsReal(sprintf("Le type du fichier uploadé %s n'est pas une image", $formImage->getMimeType()));
                }
            
                $targetedProductReference->setImageFile($formImage);
            }
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

    #[Route('/{id}/suppression', name: 'delete', methods: ['DELETE'])]
    /**
     * This method will try to soft delete targetedProduct
     * Will return a json response
     *
     * @param Request $request
     * @param SessionTokenManager $sessionTokenManager
     * @param integer $id
     * @param EntityManagerInterface $em
     * @return void
     */
    public function softDelete(Request $request, SessionTokenManager $sessionTokenManager, int $id, EntityManagerInterface $em, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer)
    {
        $authCheck = $this->checkBearerToken($request, $sessionTokenManager);
        if (!is_null($authCheck)) {
            return $authCheck;
        }

        if (is_null($id))
            return $this->makeCustomJsonErrorAsReal("Id manquant dans l'url");

        $targetedProduct = $em->getRepository(ProductReference::class)->findOneBy(['id' => $id]);

        if (is_null($targetedProduct))
            return $this->makeCustomJsonErrorAsReal(sprintf("Produit avec l'id %d inexistant", $id), 404);


        $targetedProduct->toggleDelete();
        $em->persist($targetedProduct);
        $em->flush();

        $enhancedEntityJsonSerializer
            ->setObjectToSerialize($targetedProduct)
            ->setOptions([AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn (object $product, string $format, array $context) => $product->getId()])
            ->setAttributes([
                'id',
                'name',
                'category' => ['label', 'id'],
                'createdAt',
                'updatedAt',
                'description',
                'isFavorite',
                'deletedAt',
                'productReferences' => [
                    'id',
                    'formatedPrice',
                    'weight',
                    'weightType',
                    'imageUrl',
                    'slug',
                ]
            ]);
        return $this->apiJson($enhancedEntityJsonSerializer->serialize());
    }
}
