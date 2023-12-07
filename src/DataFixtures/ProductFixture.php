<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductReference;
use App\Repository\ProductRepository;
use App\Repository\ProductCategoryRepository;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ProductFixture extends Fixture
{

    private ProductCategoryRepository $productCategoryRepository;
    private ProductRepository $productRepository;

    public function __construct(ProductCategoryRepository $productCategoryRepository, ProductRepository $productRepository)
    {
        $this->productCategoryRepository = $productCategoryRepository;
        $this->productRepository = $productRepository;
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
                $manager->persist($newProductReference);
                $manager->flush();
            }
        }
    }
}
