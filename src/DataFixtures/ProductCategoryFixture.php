<?php

namespace App\DataFixtures;

use App\Entity\ProductCategory;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

/**
 * @author Aléki <alexlegras@hotmail.com>
 * Fixture class which load product category table
 */
class ProductCategoryFixture extends Fixture implements FixtureGroupInterface
{
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
     * Will load product category table 
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $categories = [
            "Confitures de fruits",
            "Confitures de lait",
            "Confiture d'oignons",
            "Gelées",
            "Autre",
            "Jus"
        ];

        foreach($categories as $category){
            $newProductCategory = new ProductCategory;
            $newProductCategory->setLabel($category);
            $manager->persist($newProductCategory);
        }

        $manager->flush();
    }
}
