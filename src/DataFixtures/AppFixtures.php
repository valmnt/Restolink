<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $userPasswordEncoderInterface;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoderInterface)
    {
        $this->userPasswordEncoderInterface = $userPasswordEncoderInterface;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i = 0; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail($faker->email())
                ->setNom($faker->name())
                ->setPrenom($faker->firstName())
                ->setAdressePostal($faker->address())
                ->setPassword($this->userPasswordEncoderInterface->encodePassword($user, 'restolink'))
                ->setRoles(['ROLE_USER']);

            $manager->persist($user);
        }

        $user = new User();
        $user->setEmail($faker->email())
            ->setNom($faker->name())
            ->setPrenom($faker->firstName())
            ->setPassword($this->userPasswordEncoderInterface->encodePassword($user, 'restolink'))
            ->setAdressePostal($faker->address())
            ->setRoles(['ROLE_ADMIN']);

        $manager->persist($user);

        $manager->flush();
    }
}
