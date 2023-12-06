<?php

namespace App\DataFixtures;

use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Entity\ProductReference;
use App\Entity\Product;
use App\Repository\ProductReferenceRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixture extends Fixture
{

    private ProductCategoryRepository $productCategoryRepository;
    private ProductRepository $productRepository;
    private ProductReferenceRepository $productReferenceRepository;

    public function __construct(ProductCategoryRepository $productCategoryRepository, ProductRepository $productRepository, ProductReferenceRepository $productReferenceRepository)
    {
        $this->productCategoryRepository = $productCategoryRepository;
        $this->productRepository = $productRepository;
        $this->productReferenceRepository = $productReferenceRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $categoriesByCategoryLabel = [];
        $categories = $this->productCategoryRepository->createQueryBuilder("category")
            ->getQuery()
            ->execute();

        foreach($categories as $category){
            $categoriesByCategoryLabel[$category->getLabel()] = $category;
        }

        $csv = fopen(dirname(__DIR__)."\\Resources\\confiture_catalogue.csv","r");
        
        while(!feof($csv)){
            $line = fgetcsv($csv);
            $newProduct = $this->productRepository->createQueryBuilder("product")
                ->where("product.name LIKE :name")
                ->setParameter("name", $line[1])
                ->getQuery()
                ->execute();
            if (count($newProduct) < 1){
                $newProduct = new Product;
                $newProduct->setName($line[1]);
                $newProduct->setCategory($categoriesByCategoryLabel[$line[0]]);
                $manager->persist($newProduct);
                $manager->flush();
            }else{
                $newProduct = $newProduct[0];
            }
            if(count($line) > 4 ){
                $newProductReference = new ProductReference;
                $newProductReference->setProduct($newProduct);
                $newProductReference->setWeight(intval($line[2]));
                $newProductReference->setWeightType($line[3]);
                $newProductReference->setPrice(intval($line[4]));
                $newProductReference->setPicPath("/products/".$line[1].$line[2].$line[3].".jpg");
                $manager->flush();
            }
            
            if (!is_null($newProductReference)){
                $manager->persist($newProductReference);
            }
        }
    }
}
