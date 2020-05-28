<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class RestaurantController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function liste(RestaurantRepository $restaurantRepository)
    {
        $restaurants = $restaurantRepository->findAll();
        return $this->render('restaurant/index.html.twig', [
            'restaurants' => $restaurants
        ]);
    }

    /**
     * @Route("/restaurants/{id}", name="restaurant_unique")
     */
    public function unique(Restaurant $restaurant)
    {
        return $this->render('restaurant/restaurant-plat.html.twig', [
            'restaurant' => $restaurant
        ]);
    }
}
