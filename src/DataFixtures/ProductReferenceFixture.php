<?php

namespace App\DataFixtures;

use App\Entity\ProductReference;
use App\Repository\ProductRepository;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

/**
 * @author Aléki <alexlegras@hotmail.com>
 * Fixture class that load product reference table in database from a csv file
 */
class ProductReferenceFixture extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    // Arguments get inject by symfony
    public function __construct(private ProductRepository $productRepository, private SluggerInterface $slugger, private KernelInterface $kernelInterface){}

    /**
    * Return a string which containt csv path use to load product table in database 
    *
    * @return string
    */
    private function getCsvPath() : string {
        return $this->kernelInterface->getProjectDir()."\\src\\Resources\\confiture_catalogue.csv";
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
            ProductFixture::class
        ];
    }

    /**
     * Will load product reference table in database from csv
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $csvResource = fopen($this->getCsvPath(), "r");
        while(!feof($csvResource)){
            $csvRow = fgetcsv($csvResource);
            $databaseProduct = $this->productRepository->findOneBy(['name' =>  $csvRow[1]]);
            // if product is null u can't attach productRef then u skip this csv row 
            // or if csv row is incomplete (at least 5 cells in row to be completed) you skip row 
            if(is_null($databaseProduct) || count($csvRow) < 5)
                continue;

            $newProductReference = (new ProductReference)
                ->setProduct($databaseProduct)
                ->setWeight(intval($csvRow[2]))
                ->setWeightType($csvRow[3])
                ->setPrice(intval($csvRow[4]));
            
            // Img are already load on server, so we try to check if they exist, because some product ref doesn't have one,
            // if it exist we bind it in db through Vich\UploaderBundle library
            $newProductReferenceHypotheticalImgPath = sprintf("%s\public\products\%s.jpg", $this->kernelInterface->getProjectDir() ,$this->slugger->slug($newProductReference->getValueToSlugify())); 
            if (file_exists($newProductReferenceHypotheticalImgPath))
                $newProductReference->setImageFile(new ReplacingFile($newProductReferenceHypotheticalImgPath));
            $manager->persist($newProductReference);
        }
        $manager->flush();
    }

}
