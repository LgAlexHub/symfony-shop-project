<?php

namespace App\DataFixtures;

use App\Entity\OrderProductRef;
use App\Entity\ProductReference;
use App\Repository\OrderRepository;
use App\Repository\ProductReferenceRepository;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderProductRefFixture extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function __construct(
        private ProductReferenceRepository $productReferenceRepository,
        private OrderRepository $orderRepository
    ){}

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
            OrderFixture::class
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
            'order'
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
        return $this->productReferenceRepository->createQueryBuilder('pr')
            ->setMaxResults(rand(1, 10))
            ->orderBy('RAND()')
            ->getQuery()
            ->getResult();
    }

    // Will load fake item orders in database
    public function load(ObjectManager $manager): void
    {
        $orders = $this->orderRepository->findAll();
        foreach ($orders as $order) {
            $productsRefs = $this->getRandomProductRefs();
            foreach($productsRefs as $prodRef){
                $newOrderProdRef = new OrderProductRef;
                $newOrderProdRef->setQuantity(rand(1, 50));
                $newOrderProdRef->setOrder($order);
                $newOrderProdRef->setItem($prodRef);
                $manager->persist($newOrderProdRef);
            }
        }
        $manager->flush();
    }
}
