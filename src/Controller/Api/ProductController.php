<?php

namespace App\Controller\Api;

use App\Repository\ProductReferenceRepository;
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
}
