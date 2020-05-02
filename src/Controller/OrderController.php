<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeDetails;
use App\Entity\Plat;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Swift_Message;
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
        $boolSession = $this->session->has('allPlats');
        $restaurant = $plat->getRestaurant();

        if (!$boolSession) {
            $this->session->set('allPlats', $allPlats = []);
            $boolSession = $this->session->has('allPlats');
            $this->session->set('restaurant', $plat->getRestaurant()->getLibelle());
        }

        if ($boolSession && $restaurant->getLibelle() === $this->session->get('restaurant')) {

            $allPlats = $this->session->get('allPlats');

            $commandeDetails = new CommandeDetails();
            $commandeDetails->setPlats($plat);
            $commandeDetails->setPrix($plat->getPrix());

            $allPlats[$plat->getId()] = $commandeDetails;
            $this->session->set('allPlats', $allPlats);
            return $this->render('restaurant/restaurant-plat.html.twig', ['restaurant' => $restaurant]);
        } else {
            $this->addFlash('warning', 'Vous pouvez commander des plats uniquement s\'ils font partis du même restaurant.');
            return $this->redirectToRoute('restaurants_liste');
        }
    }

    /**
     * @Route("/order_bdd", name="commande_plat_order")
     */
    public function setCommandeBdd(UserRepository $userRepository, Swift_Mailer $swift_Mailer)
    {
        $user = new User();
        $user = $this->getUser();
        $allPlats = $this->session->get('allPlats');
        $solde = $user->getSolde();
        $bill = 0;
        $restaurant = '';

        if ($allPlats) {

            $commande = new Commande();
            $commande->setMembres($user);
            $commande->setAdresse($user->getAdressePostal());

            $this->em->persist($commande);

            foreach ($allPlats as $commandeDetails) {
                if ($restaurant === '') {
                    $restaurant = $commandeDetails->getPlats();
                    $restaurant = $restaurant->getRestaurant();
                    $membre = $restaurant->getMembres();

                    $membre = $userRepository->findBy(['id' => $membre->getId()]);
                }
                $bill += $commandeDetails->getPrix();
                $commandeDetails = $commandeDetails->setCommande($commande);
                $this->em->merge($commandeDetails);
            }

            if ($solde >= $bill) {

                $solde = $solde - $bill;
                $user->setSolde($solde);

                $this->em->flush();

                $this->session->remove('allPlats');
                $this->session->remove('restaurant');

                $message = (new Swift_Message('Nouvelle Commande'))
                    ->setFrom('valentinmont8@gmail.com')
                    ->setTo($membre[0]->getEmail())
                    ->setBody('Bonjour ' . $membre[0]->getNom() . ', Nous vous informons qu\'une nouvelle commande a été passé dans votre restaurant. Pour avoir le detail, nous vous invitons à vous connecter sur la plateforme. L\'équipe Restolink');

                $swift_Mailer->send($message);
                return $this->redirectToRoute('user');
            } else {
                $this->addFlash('danger', 'Oh bah le portefeuille est vide 😭');
                return $this->redirectToRoute('plat_order');
            }
        }
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
                $this->session->remove('allPlats');
                $this->session->remove('restaurant');
                return $this->redirectToRoute('restaurants_liste');
            }
        }
    }
}
