<?php

namespace App\DataFixtures;

use App\Entity\Admin;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @author Alex <alexlegras@hotmail.com>
 * Class use too seed database with 6 fake admin users
 */
class AdminFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->passwordHasher = $hasher;
    }


    public function load(ObjectManager $manager): void
    {
        $admins = array(
            array(
                'email' => 'alex@hotmail.com',
                'password' => 'azerty',
                'pseudonyme' => 'Aléki'
            ),
            array(
                'email' => 'john.doe@example.com',
                'password' => 'johnsPass123',
                'pseudonyme' => 'johnny'
            ),
            array(
                'email' => 'jane.smith@example.com',
                'password' => 'secureJanePass',
                'pseudonyme' => 'jane'
            ),
            array(
                'email' => 'bob.jones@example.com',
                'password' => 'bobsPassword',
                'pseudonyme' => 'bob'
            ),
            array(
                'email' => 'alice.white@example.com',
                'password' => 'alicePass',
                'pseudonyme' => 'alice'
            ),
            array(
                'email' => 'charlie.brown@example.com',
                'password' => 'charliesSecret',
                'pseudonyme' => 'charlie'
            ),
        );

        foreach($admins as $admin) {
            $newAdmin = new Admin;
            $newAdmin->setEmail($admin['email']);
            $newAdmin->setPseudonyme($admin['pseudonyme']);
            $newAdmin->setPassword($this->passwordHasher->hashPassword($newAdmin, $admin['password']));
            $manager->persist($newAdmin);
        }
        $manager->flush();
    }
}
