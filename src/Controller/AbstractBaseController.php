<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PlatRepository;
use App\Repository\RestaurantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AbstractBaseController extends AbstractController
{
    public function getHistoricUserCommandes(PlatRepository $platRepository, RestaurantRepository $restaurantRepository, $render, $redirect, User $user)
    {
        $commandeStatus = [];
        $arrayAllCommandeDetails = [];
        $commandeDetails = [];
        $commandes = $user->getCommandes();

        if ($commandes) {
            foreach ($commandes as $commande) {
                $commandeDetailsLocal = $commande->getCommandeDetails();
                $commandeStatus[] = $commande->getStatus();
                foreach ($commandeDetailsLocal as $commandeDetail) {
                    $commandeDetailPlat = $commandeDetail->getPlats();
                    $commandeDetailRestaurant = $commandeDetailPlat->getRestaurant();
                    $restaurantRepository->findBy(['id' => $commandeDetailRestaurant->getId()]);
                    $platRepository->findBy(['id' => $commandeDetailPlat->getId()]);

                    array_push($commandeDetails, $commandeDetailPlat);
                }
                array_push($arrayAllCommandeDetails, $commandeDetails);
                $commandeDetails = [];
            }

            $role = $user->getRoles();
            return $this->render($render, [
                'user' => $user,
                'role' => $role,
                'commandeDetails' => $arrayAllCommandeDetails,
                'status' => $commandeStatus
            ]);
        } else {
            return $this->redirectToRoute($redirect);
        }
    }
}
