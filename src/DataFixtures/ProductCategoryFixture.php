<?php

namespace App\DataFixtures;

use App\Entity\ProductCategory;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;


/**
 * @author Aléki <alexlegras@hotmail.com>
 * Fixture class which load 6 default category
 */
class ProductCategoryFixture extends Fixture
{
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
