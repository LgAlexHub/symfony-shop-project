<?php

namespace App\DataFixtures;

use App\Entity\Order;

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

    public function __construct()
    {
        $this->fakerGenerator = Factory::create();
        $this->maxCreate = 150  ;
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
            'order'
        ];
    }

    /**
     * Will load fake order in database
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {       
        $startDate = new \DateTimeImmutable('2023-01-01 12:00:00');
        $endDate = new \DateTimeImmutable('2023-06-20 23:59:00');
        $events = $manager->getClassMetadata(Order::class)->lifecycleCallbacks;
        $manager->getClassMetadata(Order::class)->setLifecycleCallbacks(array());
        for ($i=0; $i < $this->maxCreate; $i++) { 
            $randomDate = new \DateTimeImmutable('@' . mt_rand($startDate->getTimestamp(), $endDate->getTimestamp()));
            $order = (new Order);
            $order
                ->setUuid(Uuid::v4())
                ->setClientFirstName($this->fakerGenerator->firstName())
                ->setClientLastName($this->fakerGenerator->lastName())
                ->setEmail($this->fakerGenerator->email())
                ->setComment($this->fakerGenerator->boolean() ? $this->fakerGenerator->realText() : null)
                ->setIsDone($this->fakerGenerator->boolean())
                ->setCreatedAt($randomDate)
                ->setConsentData(true)
                ->setUpdatedAt($randomDate);
            
            $manager->persist($order);
        }
        $manager->flush();
        $manager->getClassMetadata(Order::class)->setLifecycleCallbacks($events);

    }
}
