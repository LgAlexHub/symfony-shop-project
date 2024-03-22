<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\OrderProductRef;
use App\Repository\ProductReferenceRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Uid\Uuid;

//TODO : Implement FixtureGroupInterface to load fixture in right order
class ZOrderFixture extends Fixture
{

    protected Generator $fakerGenerator;
    protected int $maxCreate;

    public function __construct(protected ProductReferenceRepository $prodRefRep)
    {
        $this->fakerGenerator = Factory::create();
        $this->maxCreate = 20;
    }

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
            
            $productsRefs = $this->prodRefRep->createQueryBuilder('pr')
                ->setMaxResults($this->fakerGenerator->numberBetween(1, 10))
                ->orderBy('RAND()')
                ->getQuery()
                ->getResult();
            foreach($productsRefs as $prodRef){
                $newOrderProdRef = new OrderProductRef;
                $newOrderProdRef->setQuantity($this->fakerGenerator->numberBetween(1,15));
                $newOrderProdRef->setOrder($order);
                $newOrderProdRef->setItem($prodRef);
                $manager->persist($newOrderProdRef);
            }
            $manager->flush();

        }
        
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
