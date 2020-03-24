<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\User;
use App\Form\RestaurantType;
use App\Utils\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RestaurateurController extends AbstractController
{
    /**
     * @Route("/mes_restaurants", name="mes_restaurants")
     */
    public function index()
    {
        $user = $this->getUser();

        return $this->render('restaurateur/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/add_restaurant", name="add__restaurant")
     */
    public function addUserRestaurants(Request $request, EntityManagerInterface $entityManagerInterface, UploadService $uploadService)
    {
        $restaurant = new Restaurant();
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);
        $user = new User();
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $restaurant->setMembres($user);

            $directory = $this->getParameter('upload_directory');
            $fileName = $uploadService->uploadImage($form, 'image', $directory);

            $restaurant->setImage('/uploads/' . $fileName);
            $entityManagerInterface->persist($restaurant);
            $entityManagerInterface->flush();
        }

        return $this->render('restaurateur/add-restaurant.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}
