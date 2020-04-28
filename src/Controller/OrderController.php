<?php

namespace App\Controller;

use App\Entity\Plat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/order", name="plat_order")
     */
    public function index()
    {
        $allPlats = $this->session->get('allPlats');
        if ($allPlats) {
            return $this->render('order/index.html.twig', ['plat' => $allPlats]);
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
            $allPlats[] = $plat;
            $this->session->set('allPlats', $allPlats);
        } else {
            $this->session->set('allPlats', $allPlats = []);
        }

        return $this->render('restaurant/restaurant-plat.html.twig', ['restaurant' => $restaurant]);
    }
}
