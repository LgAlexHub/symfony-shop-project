<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\ProductCategoryRepository;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * @author Aléki <alexlegras@hotmail.com>
 * Fixture class that load product table database from a csv file
 */
class ProductFixture extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{

    // Arguments get inject by symfony
    public function __construct(private ProductCategoryRepository $productCategoryRepository, private ProductRepository $productRepository){}

    /**
     * Return a string which containt csv path use to load product table in database 
     *
     * @return string
     */
    private static function getCsvPath() : string {
        return dirname(__DIR__)."\\Resources\\confiture_catalogue.csv";
    }

   /**
     * Returns the groups this fixture belongs to.
     *
     * This method is required by the FixtureGroupInterface and is used to specify
     * the groups to which this fixture belongs. These groups can be used to control
     * the execution of fixtures based on environment or functionality.
     *
     * @return array
     */
    public static function getGroups(): array
    {
        return [
            'dev',
            'prod',
            'product'
        ];
    }

    /**
     * Returns the list of fixtures classes on which the current fixture depends.
     *
     * This method is required by the DependentFixtureInterface and is used to specify
     * the dependencies of the current fixture. It returns an array containing the fully
     * qualified class names of the fixtures on which the current fixture depends.
     *
     * For example, if the current fixture depends on the ProductCategoryFixture class,
     * it should return an array containing ProductCategoryFixture::class.
     *
     * @return array The list of fixture classes on which the current fixture depends.
     */
    public function getDependencies() : array
    {
        return [
            ProductCategoryFixture::class
        ];
    }

    /**
     * Will load product table in database from csv
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $csvResource = fopen(self::getCsvPath(), "r");

        // Use to label array keys with category label
        $categories = array_reduce(
            $this->productCategoryRepository->findALl(),
            fn($accumulator, $category) => array_merge($accumulator, [$category->getLabel() => $category]),
            []
        );

        while(!feof($csvResource)){
            $csvRow = fgetcsv($csvResource);
            $databaseProduct = $this->productRepository->findOneBy(['name' =>  $csvRow[1]]);
            // Inside csv, one line per product reference, this condition will prevent from adding products with same name
            if (!is_null($databaseProduct))
                continue;
            $newProduct = new Product;
            $newProduct
                ->setName($csvRow[1])
                ->setIsFavorite(false)
                ->setCategory($categories[$csvRow[0]]);
            $manager->persist($newProduct);
            $manager->flush();
        }
    }
}
