<?php

namespace App\DataFixtures;

use App\Entity\Plat;
use App\Entity\Restaurant;
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

        for ($i = 0; $i <= 25; $i++) {
            $resto = new Restaurant();
            $resto->setDescription($faker->text())
                ->setLibelle($faker->word())
                ->setImage('https://source.unsplash.com/random/400x300');
            $manager->persist($resto);
            for ($j = 0; $j < 25; $j++) {
                $plat = new Plat();
                $plat->setLibelle($faker->word())
                    ->setPrix($faker->numberBetween(1.50, 50))
                    ->setRestaurant($resto)
                    ->setImage('https://source.unsplash.com/random/400x300');
                $manager->persist($plat);
            }
        }

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
