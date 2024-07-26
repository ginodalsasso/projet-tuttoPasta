<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');
        // Crée un utilisateur
        $user = new User();

        $user->setEmail('user@test.com')
             ->setUsername($faker->userName());
        // Hash du mot de passe
        $password = $this->hasher->hashPassword($user, 'password');
        $user->setPassword($password);    

        $manager->persist($user); 
        $manager->flush();

    }
}
