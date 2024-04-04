<?php

namespace App\Controller\Admin\Api;

use App\Controller\Admin\Api\ApiAdminController;
use App\Repository\ProductRepository;
use App\Service\EnhancedEntityJsonSerializer;
use App\Service\SessionTokenManager;

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
    public function listOrdersWithPaginationAndFilters(Request $request, SessionTokenManager $sessionTokenManager, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer, ProductRepository $productManager) : Response {
        $this->checkBearerToken($request, $sessionTokenManager);
        $page = $request->get('page') ?? 1;
        $query = $request->get('query') ?? '';
        $products = $productManager->paginateFilterProducts(page: $page, userSearchQuery: $query);
     
        $enhancedEntityJsonSerializer
            ->setObjectToSerialize($products->results)
            ->setOptions([AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn (object $product, string $format, array $context) => $product->getId()])
            ->setAttributes([
               'id',
               'name',
               'category' => ['label'],
               'createdAt',
               'productReferences' => [
                    'price',
                    'weight',
                    'weighType',
                    'imageUrl',
                    'slug'
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
}
