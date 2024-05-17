<?php

namespace App\Controller\Admin\Api;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\SessionTokenManager;
use App\Service\EnhancedEntityJsonSerializer;
use App\Controller\Admin\Api\ApiAdminController;

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
    public function listProductsWithPaginationAndFilters(Request $request, SessionTokenManager $sessionTokenManager, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer, ProductRepository $productManager) : Response {
        $authCheck = $this->checkBearerToken($request, $sessionTokenManager);
        if(!is_null($authCheck)){
            return $authCheck;
        }
        $page = $request->get('page') ?? 1;
        $query = $request->get('query') ?? '';
        $cat = $request->get('cat') ?? null;
        $products = $productManager->paginateFilterProducts(page: $page, userSearchQuery: $query, category: $cat);
        
        $enhancedEntityJsonSerializer
            ->setObjectToSerialize($products->results)
            ->setOptions([AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn (object $product, string $format, array $context) => $product->getId()])
            ->setAttributes([
               'name',
               'category' => ['label', 'id'],
               'createdAt',
               'updatedAt',
               'description',
               'productReferences' => [
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

    #[Route('/{id}', name: 'edit', methods:['GET', 'PATCH'])]
    public function edit(Request $request, SessionTokenManager $sessionTokenManager, EntityManagerInterface $em, EnhancedEntityJsonSerializer $enhancedEntityJsonSerializer, int $id){
        $authCheck = $this->checkBearerToken($request, $sessionTokenManager);
        if(!is_null($authCheck)){
            return $authCheck;
        }

        if (is_null($id))
            return $this->json(['error' => ['msg' => sprintf("Id manquant dans l'url")]], 422);

        $targetedProduct = $em->getRepository(Product::class)->findOneBy(['id' => $id]);

        if (is_null($targetedProduct))
            return  $this->json(['error' => ['msg' => sprintf("Produit avec l'id %d inexistant", $id)]], 404);
        
        $productForm = $this->createForm(ProductType::class, options: ['csrf_protection' => false]);
        $productForm->submit(json_decode($request->getContent(), true));
        if($productForm->isValid()){           
            $editedProduct = $productForm->getData();
            $targetedProduct
                ->setName($editedProduct->getName())
                ->setDescription($editedProduct->getDescription())
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
            return $this->apiJson($enhancedEntityJsonSerializer->serialize());
        }
        //TODO : ajouter un vrai feeback
        return  $this->json(['error' => ['msg' => 'Formulaire invalide !']], 422);
    }
}
    