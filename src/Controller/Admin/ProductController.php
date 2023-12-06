<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\ProductReference;
use App\Form\ProductFormType;
use App\Form\ProductReferenceType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/products', 'admin.products.')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $manager): Response
    {
        $products = $manager->getRepository(Product::class)
            ->createQueryBuilder("product")
            ->orderBy("product.name", "ASC")
            ->getQuery()
            ->execute();
        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function create(Request $request, EntityManagerInterface $manager){
        $productCategories = $manager->getRepository(ProductCategory::class)
            ->createQueryBuilder("cat")
            ->getQuery()
            ->getResult();

        $productForm = $this->createForm(ProductFormType::class, options:[
            'categories' => $productCategories,
        ]);

        $productForm->handleRequest($request);

        if($productForm->isSubmitted() && $productForm->isValid()){
            $newProduct = $productForm->getData();
            $manager->persist($newProduct);
            $manager->flush();
            $manager->detach($newProduct);
            return $this->redirectToRoute('admin.products.home');
        }

        return $this->render('admin/product/create.html.twig', [
            'form' => $productForm
        ]);
    }
   
    #[Route('/delete/{id}', name:'delete')]
    /**
     * Try to delete a product by id 
     * Note : 
     *  * redirect_to "admin.products.home" route if delete is successful
     * TDOO : add 404 exception if id not find
     * @param Request $request
     * @param integer $id
     * @param EntityManagerInterface $entityManager
     * @return void
     */
    public function delete(EntityManagerInterface $entityManager, int $id){
        $productRepository = $entityManager->getRepository(Product::class);
        $product = $productRepository->find($id);
        $productsReferencesRepository = $entityManager->getRepository(ProductReference::class);
        $productsReferences = $productsReferencesRepository
            ->createQueryBuilder("proref")
            ->where("proref.product = :product_id")
            ->setParameter("product_id", $id)
            ->getQuery()
            ->execute();
        $this->checkEntityExistence($product, $id);
        foreach($productsReferences as $prodRef){
            $entityManager->remove($prodRef);
        }
        $entityManager->remove($product);
        $entityManager->flush();
        return $this->redirectToRoute("admin.products.home");
    }

    #[Route('/edit/{id}', name : 'edit')]
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager){
        $product = $entityManager->getRepository(Product::class)
            ->find($id);
        $this->checkEntityExistence($product, $id);
        $productCategories = $entityManager->getRepository(ProductCategory::class)
            ->createQueryBuilder("cat")
            ->getQuery()
            ->getResult();

        $productForm = $this->createForm(ProductFormType::class, $product, [
            'categories' => $productCategories
        ]);
        $productForm->handleRequest($request);
        if ($productForm->isSubmitted() && $productForm->isValid()){
            $updatedProduct = $productForm->getData();
            $product->setName($updatedProduct->getName());
            $product->setCategory($updatedProduct->getCategory());
            $product->setUpdatedAt(new DateTime());
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('admin.products.home');
        }
        return $this->render('admin/product/edit.html.twig', [
            'form' => $productForm
        ]);
    }

    #[Route('/{id}/prices', name : 'prices')]
    public function showPrices(Request $request, int $id, EntityManagerInterface $manager){
        $newRef = new ProductReference;
        $product = $manager->getRepository(Product::class)->find($id);
        $this->checkEntityExistence($product, $id);
        $refForm = $this->createForm(ProductReferenceType::class, options: []);
        $refForm->handleRequest($request);
        if($refForm->isSubmitted() && $refForm->isValid()){
            $fomatedPrice = floatval(preg_replace('/\,/', '.', $request->get('product_reference')['price'] ?? 0)) * 100 ;
            $newRef = $refForm->getData();
            $newRef->setProduct($product);
            $newRef->setPrice($fomatedPrice);
            $manager->persist($newRef);
            $manager->flush();
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render('admin/product/reference/index.html.twig', [
                'product' => $product,
            'refForm' => $refForm
        ]);
    }

    #[Route('/{productId}/prices/{productRefId}', name : 'prices.delete')]
    public function deletePrice(Request $request, EntityManagerInterface $manager, int $productRefId){
        $ref = $manager->getRepository(ProductReference::class)->find($productRefId);
        $this->checkEntityExistence($ref, $productRefId);
        $manager->remove($ref);
            $manager->flush();
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/{productId}/prices/{productRefId}/edit', name : 'prices.edit')]
    public function editPrice(Request $request, EntityManagerInterface $manager, int $productRefId){
        $ref = $manager->getRepository(ProductReference::class)->find($productRefId);
        $this->checkEntityExistence($ref, $productRefId);
        $productRefForm = $this->createForm(ProductReferenceType::class, $ref);
        $productRefForm->handleRequest($request);
        if ($productRefForm->isSubmitted() && $productRefForm->isValid()){
            $fomatedPrice = floatval(preg_replace('/\,/', '.', $request->get('product_reference')['price'] ?? 0)) * 100 ;
            $updatedProductRef = $productRefForm->getData();
            $ref->setWeight($updatedProductRef->getWeight());
            $ref->setWeightType($updatedProductRef->getWeightType());
            $ref->setPrice($fomatedPrice);
            $manager->persist($ref);
            $manager->flush();
            return $this->redirectToRoute('admin.products.prices', ['id' => $ref->getProduct()->getId()]);
        }

        return $this->render('admin/product/reference/edit.html.twig', [
            'refForm' => $productRefForm
        ]);
    }

    private function checkEntityExistence(mixed $var, int $id){
        if (!$var){
            throw $this->createNotFoundException(
                'No entity found for id '.$id
            );
        }
    }
}