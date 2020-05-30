<?php

namespace App\DataFixtures;

use App\Entity\Plat;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Utils\UploadService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $userPasswordEncoderInterface;
    private $uploadService;
    private $params;

    public function __construct(UploadService $uploadService, UserPasswordEncoderInterface $userPasswordEncoderInterface, ParameterBagInterface $params)
    {
        $this->userPasswordEncoderInterface = $userPasswordEncoderInterface;
        $this->uploadService = $uploadService;
        $this->params = $params;
    }

    public function roleUser($faker, $role, $manager)
    {
        for ($i = 0; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail($faker->email())
                ->setNom($faker->name())
                ->setPrenom($faker->firstName())
                ->setPassword($this->userPasswordEncoderInterface->encodePassword($user, 'restolink'))
                ->setAdressePostal($faker->address())
                ->setRoles($role);

            $manager->persist($user);
        }
    }
    public function createFakeImage($faker)
    {
        $filesystem = new Filesystem();
        $faker = Factory::create();
        $baseImages = [];
        $baseImages[] = $this->params->get('kernel.project_dir') . '/src/DataFixtures/images/bk.jpeg';
        $baseImages[] = $this->params->get('kernel.project_dir') . '/src/DataFixtures/images/mcdo.jpeg';
        $baseImages[] = $this->params->get('kernel.project_dir') . '/src/DataFixtures/images/subway.jpeg';

        $imageName = $this->params->get('kernel.project_dir') . '/src/DataFixtures/images/image.jpeg';
        
        $filesystem->copy($faker->randomElement($baseImages), $imageName);

        return $this->uploadService->uploadImage(new File($imageName));
        

    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ($i = 0; $i < 25; $i++) {

            $resto = new Restaurant();
            $user = new User();

            $user->setEmail($faker->email())
                ->setNom($faker->name())
                ->setPrenom($faker->firstName())
                ->setAdressePostal($faker->address())
                ->setPassword($this->userPasswordEncoderInterface->encodePassword($user, 'restolink'))
                ->setRoles(['ROLE_RESTAURATEUR']);
            $manager->persist($user);
            $resto->setDescription($faker->text())
                ->setLibelle($faker->word())
                ->setAdresse($faker->address())
                ->setIsValide($faker->boolean(80))
                ->setImage($this->createFakeImage($faker))
                ->setMembres($user);
            $manager->persist($resto);

            for ($j = 0; $j < 25; $j++) {
                $plat = new Plat();
                $plat->setLibelle($faker->word())
                    ->setPrix($faker->numberBetween(1.50, 50))
                    ->setRestaurant($resto)
                    ->setImage($this->createFakeImage($faker));
                $manager->persist($plat);
            }
        }

        // $this->roleUser($faker, ['ROLE_ADMIN'], $manager);
        // $this->roleUser($faker, ['ROLE_USER'], $manager);
        $user->setEmail('admin@admin.com')
                ->setNom('admin')
                ->setPrenom('admin')
                ->setAdressePostal('Adresse admin')
                ->setPassword($this->userPasswordEncoderInterface->encodePassword($user, 'restolink'))
                ->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        $manager->flush();
    }
}
