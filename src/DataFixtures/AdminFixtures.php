<?php

namespace App\DataFixtures;

use App\Entity\Admin;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @author Alex <alexlegras@hotmail.com>
 * Class use too seed database with 6 fake admin users
 */
class AdminFixtures extends Fixture implements FixtureGroupInterface
{

    public function __construct(private UserPasswordHasherInterface $hasher){}

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
     * Will load fake admin into database
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $admins = array(
            (object) array(
                'email' => 'alex@hotmail.com',
                'password' => 'azerty',
                'pseudonyme' => 'Aléki'
            ),
            (object) array(
                'email' => 'john.doe@example.com',
                'password' => 'johnsPass123',
                'pseudonyme' => 'johnny'
            ),
            (object) array(
                'email' => 'jane.smith@example.com',
                'password' => 'secureJanePass',
                'pseudonyme' => 'jane'
            ),
            (object) array(
                'email' => 'bob.jones@example.com',
                'password' => 'bobsPassword',
                'pseudonyme' => 'bob'
            ),
            (object) array(
                'email' => 'alice.white@example.com',
                'password' => 'alicePass',
                'pseudonyme' => 'alice'
            ),
            (object) array(
                'email' => 'charlie.brown@example.com',
                'password' => 'charliesSecret',
                'pseudonyme' => 'charlie'
            ),
        );

        foreach($admins as $admin) {
            $newAdmin = (new Admin());
            $newAdmin->setEmail($admin->email);
            $newAdmin->setPseudonyme($admin->pseudonyme);
            $newAdmin->setPassword($this->hasher->hashPassword($newAdmin, $admin->password));
            $manager->persist($newAdmin);
        }
        $manager->flush();
    }
}
