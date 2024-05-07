<?php

namespace App\Controller\Api;

use App\Entity\ProductCategory;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductReferenceRepository;
use App\Repository\ProductRepository;
use App\Service\EnhancedEntityJsonSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('api/produits', 'api.products.')]
class ProductController extends AbstractController
{
    #[Route('/recherche', name: 'query')]
    /**
     * API Endpoint: get product refrences by name.
     *
     * @param ProductReferenceRepository $productReferenceRepository
     * @param Request $request
     * @param EnhancedEnityJsonSerializer $enhancedEnityJsonSerializer
     * @return Response
     */
    public function searchProductReferencesByQuery(ProductReferenceRepository $productReferenceRepository, Request $request, EnhancedEntityJsonSerializer $enhancedEnityJsonSerializer): Response
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
    public function listProductsWithPaginationAndFilters(Request $request, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer, ProductRepository $productManager) : Response {
        $page = $request->get('page') ?? 1;
        $query = $request->get('query') ?? '';
        $cat = $request->get('cat') ?? null;
        $products = $productManager->paginateFilterProducts(page: $page, userSearchQuery: $query, category: $cat);
        
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
               'productReferences' => [
                    'id',
                    'formatedPrice',
                    'weight',
                    'weightType',
                    'imageUrl',
                    'slug',
               ]
            ]);
        return new Response(
            sprintf("{\n\t\"products\": %s,
                \"totalPage\": %d,
                \"nbResult\": %d,
                \"page\": %d
            }", $enhancedEntityJsonSerializer->serialize(), $products->maxPage, $products->nbResult, $products->page), 
            headers: [ 'Content-Type' => 'application/json']
        );
    }

    #[Route('/categories', name: 'categories.list')]
    public function categoriesList(EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer, ProductCategoryRepository $manager){
        $enhancedEntityJsonSerializer
            ->setObjectToSerialize($manager->findCategoryWithProduct())
            ->setOptions([AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn (object $orderProductRef, string $format, array $context) => $orderProductRef->getId()])
            ->setAttributes([
                'id', 'label'
            ]);
        return new Response(
            $enhancedEntityJsonSerializer->serialize(), 
            headers: [ 'Content-Type' => 'application/json']
        ); 
    }
}
