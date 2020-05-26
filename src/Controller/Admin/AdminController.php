<?php

namespace App\Controller\Admin;

use App\Repository\CommandeRepository;
use App\Repository\RestaurantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(CommandeRepository $commandeRepository, RestaurantRepository $restaurantRepository)
    {
        $sumFraisLivraison = $commandeRepository->sumFraisLivraison()[0]['sumFraisLivraison'];
        $countRestaurant = $restaurantRepository->count([]);
        $countCommandeNonLivre = $commandeRepository->count(["status" => 0]);
        $countCommandeLivre = $commandeRepository->count(["status" => 1]);
        return $this->render('admin/index.html.twig',[
            "sumFraisLivraison" => $sumFraisLivraison,
            "countRestaurant" => $countRestaurant,
            "countCommandeNonLivre" => $countCommandeNonLivre,
            "countCommandeLivre" => $countCommandeLivre
        ]
    );
    }
}