<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeDetails;
use App\Entity\Plat;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private $session;
    private $em;

    public function __construct(SessionInterface $session, EntityManagerInterface $em)
    {
        $this->session = $session;
        $this->em = $em;
    }

    /**
     * @Route("/order", name="plat_order")
     */
    public function index()
    {
        $allPlats = $this->session->get('allPlats');
        if ($allPlats) {
            return $this->render('order/index.html.twig', ['plats' => $allPlats]);
        } else {
            return $this->redirectToRoute('restaurants_liste');
        }
    }

    /**
     * @Route("/order/{id}", name="add_plat_order")
     */
    public function addPlatSession(Plat $plat)
    {
        $restaurant = $plat->getRestaurant();
        if ($this->session->has('allPlats')) {

            $allPlats = $this->session->get('allPlats');

            $commandeDetails = new CommandeDetails();
            $commandeDetails->setPlats($plat);
            $commandeDetails->setPrix($plat->getPrix());

            $allPlats[$plat->getId()] = $commandeDetails;
            $this->session->set('allPlats', $allPlats);
        } else {
            $this->session->set('allPlats', $allPlats = []);
        }

        return $this->render('restaurant/restaurant-plat.html.twig', ['restaurant' => $restaurant]);
    }

    /**
     * @Route("/order_bdd", name="commande_plat_order")
     */
    public function setCommandeBdd()
    {
        $allPlats = $this->session->get('allPlats');

        if ($allPlats) {

            $commande = new Commande();
            $commande->setMembres($this->getUser());

            $this->em->persist($commande);

            foreach ($allPlats as $plat) {
                $plat = $plat->setCommande($commande);
                $this->em->merge($plat);
            }
            $this->em->flush();

            $this->session->remove('allPlats');
        }

        return $this->redirectToRoute('user');
    }


    /**
     * @Route("/delete_plat_order/{id}", name="delete_plat_order")
     */
    public function deletePlatOrder(Plat $plat)
    {
        $allPlats = $this->session->get('allPlats');

        if ($allPlats) {
            foreach ($allPlats as $platDelete) {
                $platDeleteId = $platDelete->getPlats()->getId();

                if ($platDeleteId === $plat->getId()) {
                    unset($allPlats[$platDeleteId]);
                    $this->session->set('allPlats', $allPlats);
                    $allPlats = $this->session->get('allPlats');
                }
            }
            if ($allPlats) {
                return $this->render('order/index.html.twig', ['plats' => $allPlats]);
            } else {
                return $this->redirectToRoute('restaurants_liste');
            }
        }
    }
}
