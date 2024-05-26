<?php

namespace App\Controller\Admin\Api;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\SessionTokenManager;
use App\Service\EnhancedEntityJsonSerializer;
use App\Controller\Admin\Api\ApiAdminController;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/admin/produits', 'api.admin.products.')]
/**
 * @author Alélki <alexlegras@hotmail.com>
 * @version 1.0.0
 */
class ProductController extends ApiAdminController
{
    #[Route('/', name: 'list')]
    /**
     * This method will render a paginate and filter list of product in json format.
     *
     * @param Request $request
     * @param SessionTokenManager $sessionTokenManager
     * @param EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer
     * @param ProductRepository $productManager
     * @return Response
     */
    public function listProductsWithPaginationAndFilters(Request $request, SessionTokenManager $sessionTokenManager, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer, ProductRepository $productManager): Response
    {
        $authCheck = $this->checkBearerToken($request, $sessionTokenManager);
        if (!is_null($authCheck)) {
            return $authCheck;
        }
        $page = $request->get('page', 1);
        $query = $request->get('query', '');
        $softDelete = $request->get('deleted', 0);
        $isFavorite = $request->get('favorite', 0);
        $cat = $request->get('cat', null);
        if (!in_array($softDelete, [0, 1, -1])) {
            $softDelete = 0;
        }
        $products = $productManager->paginateFilterProducts(page: $page, userSearchQuery: $query, category: $cat, withSoftDelete: $softDelete, isAdmin: true, adminFavoriteFilter: $isFavorite);

        $enhancedEntityJsonSerializer
            ->setObjectToSerialize($products->results)
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
                    'deletedAt'
                ]
            ]);
        return new Response(
            sprintf("{\n\t\"products\": %s,
                \"totalPage\": %d,
                \"nbResult\": %d,
                \"page\": %d
            }", $enhancedEntityJsonSerializer->serialize(), $products->maxPage, $products->nbResult, $products->page),
            headers: ['Content-Type' => 'application/json']
        );
    }

    #[Route('/{id}', name: 'edit', methods: ['GET', 'PATCH'])]
    public function edit(Request $request, SessionTokenManager $sessionTokenManager, EntityManagerInterface $em, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer, int $id)
    {
        $authCheck = $this->checkBearerToken($request, $sessionTokenManager);
        if (!is_null($authCheck)) {
            return $authCheck;
        }

        if (is_null($id))
            return $this->json(['error' => ['msg' => sprintf("Id manquant dans l'url")]], 422);

        $targetedProduct = $em->getRepository(Product::class)->findOneBy(['id' => $id]);

        if (is_null($targetedProduct))
            return  $this->json(['error' => ['msg' => sprintf("Produit avec l'id %d inexistant", $id)]], 404);

        $productForm = $this->createForm(ProductType::class, options: ['csrf_protection' => false]);
        $productForm->submit(json_decode($request->getContent(), true));
        if ($productForm->isValid()) {
            $editedProduct = $productForm->getData();
            $targetedProduct
                ->setName($editedProduct->getName())
                ->setCategory($editedProduct->getCategory());
            $em->persist($targetedProduct);
            $em->flush();
            $em->detach($targetedProduct);
            $enhancedEntityJsonSerializer->setObjectToSerialize($targetedProduct)
                ->setAttributes([
                    'id',
                    'name',
                    'category' => ['label', 'id'],
                    'createdAt',
                    'updatedAt',
                    'deletedAt',
                    'description',
                    'productReferences' => [
                        'id',
                        'formatedPrice',
                        'weight',
                        'weightType',
                        'imageUrl',
                        'slug',
                        'deletedAt',
                    ]
                ]);
            return $this->apiJson($enhancedEntityJsonSerializer->serialize());
        }
        return  $this->json(['error' => ['msg' => $productForm->getErrors(true)]], 422);
    }

    #[Route('/{id}/favoris', name: 'favorite')]
    /**
     * This method will try to set the property isFavorite of targeted product.
     * Will return a json response
     * @param Request $request
     * @param SessionTokenManager $sessionTokenManager
     * @param integer $id
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function updateIsDone(Request $request, SessionTokenManager $sessionTokenManager, int $id, EntityManagerInterface $em, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer): Response
    {
        $auth = $this->checkBearerToken($request, $sessionTokenManager);
        // Retrun json api error 401 if auth not valid
        if (!is_null($auth)) {
            return $auth;
        }

        if (is_null($id))
            return $this->makeCustomJsonErrorAsReal("Id manquant dans l'url");

        $targetedProduct = $em->getRepository(Product::class)->findOneBy(['id' => $id]);

        if (is_null($targetedProduct))
            return $this->makeCustomJsonErrorAsReal(sprintf("Produit avec l'id %d inexistant", $id), 404);

        $targetedProduct->setIsFavorite(!$targetedProduct->getIsFavorite());
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
                    'deletedAt'
                ]
            ]);
        return $this->apiJson($enhancedEntityJsonSerializer->serialize());
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

        $targetedProduct = $em->getRepository(Product::class)->findOneBy(['id' => $id]);

        if (is_null($targetedProduct))
            return $this->makeCustomJsonErrorAsReal(sprintf("Référence avec l'id %d inexistant", $id), 404);


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
                    'deletedAt'
                ]
            ]);
        return $this->apiJson($enhancedEntityJsonSerializer->serialize());
    }
}
