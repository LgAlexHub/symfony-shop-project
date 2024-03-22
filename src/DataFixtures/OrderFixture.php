<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\OrderProductRef;
use App\Entity\ProductReference;
use App\Repository\ProductReferenceRepository;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\Uid\Uuid;

use Faker\Factory;
use Faker\Generator;

/**
 * @author Aléki <alexlegras@hotmail.com>
 * Fixture class that load fake order in database
 */
class OrderFixture extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{

    private Generator $fakerGenerator;
    private int $maxCreate;

    public function __construct(private ProductReferenceRepository $prodRefRep)
    {
        $this->fakerGenerator = Factory::create();
        $this->maxCreate = 20;
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
    public function getDependencies(){
        return [
            ProductReferenceFixture::class
        ];
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
        ];
    }

    /**
     * Retrieve a random list of product references.
     *
     * This method retrieves a random list of product references from the database.
     *
     * @return ProductReference[] An array containing the retrieved product references.
     */
    protected function getRandomProductRefs() : array {
        return $this->prodRefRep->createQueryBuilder('pr')
            ->setMaxResults($this->fakerGenerator->numberBetween(1, 10))
            ->orderBy('RAND()')
            ->getQuery()
            ->getResult();
    }

    /**
     * Will load fake order in database
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {       
        for ($i=0; $i < $this->maxCreate; $i++) { 
            $order = (new Order);
            $order
                ->setUuid(Uuid::v4())
                ->setClientFirstName($this->fakerGenerator->firstName())
                ->setClientLastName($this->fakerGenerator->lastName())
                ->setEmail($this->fakerGenerator->email())
                ->setComment($this->fakerGenerator->boolean() ? $this->fakerGenerator->realText() : null)
                ->setAdressCityCode($this->fakerGenerator->numberBetween(0, 99999))
                ->setAdressCity($this->fakerGenerator->city())
                ->setAdressStreetInfo($this->fakerGenerator->streetName())
                ->setIsDone($this->fakerGenerator->boolean());
            
            $manager->persist($order);
            $manager->flush();
            
            $productsRefs = $this->getRandomProductRefs();
            foreach($productsRefs as $prodRef){
                $newOrderProdRef = new OrderProductRef;
                $newOrderProdRef->setQuantity($this->fakerGenerator->numberBetween(1,15));
                $newOrderProdRef->setOrder($order);
                $newOrderProdRef->setItem($prodRef);
                $manager->persist($newOrderProdRef);
            }
            $manager->flush();
        }
    }
}
