<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeDetails;
use App\Entity\Plat;
use App\Entity\User;
use App\Repository\UserRepository;
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
        $commandeSession = $this->session->get('commandeSession');
        if ($commandeSession) {
            return $this->render('order/index.html.twig', ['commandeSession' => $commandeSession]);
        } else {
            $this->addFlash('warning', 'Veuillez choisir de bons petits plats 😋');
            return $this->redirectToRoute('restaurants_liste');
        }
    }

    /**
     * @Route("/order/{id}", name="add_plat_order")
     */
    public function addPlatSession(Plat $plat)
    {
        $boolSession = $this->session->has('commandeSession');
        $restaurant = $plat->getRestaurant();

        if (!$boolSession) {
            $this->session->set('commandeSession', $commandeSession = []);
            $boolSession = $this->session->has('commandeSession');
            $this->session->set('restaurant', $plat->getRestaurant()->getLibelle());
        }

        if ($boolSession && $restaurant->getLibelle() === $this->session->get('restaurant')) {

            $commandeSession = $this->session->get('commandeSession');

            $commandeDetails = new CommandeDetails();
            $commandeDetails->setPlats($plat);
            $commandeDetails->setPrix($plat->getPrix());

            $commandeSession[$plat->getId()] = $commandeDetails;
            $this->session->set('commandeSession', $commandeSession);
            $this->addFlash('success', $plat->getLibelle() . ' a été ajouté au panier 🛒');
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
        $commandeSession = $this->session->get('commandeSession');
        $solde = $user->getSolde();
        $bill = 0;
        $restaurant = '';
        $platsName = [];

        if ($commandeSession) {

            $commande = new Commande();
            $commande->setMembres($user);
            $commande->setAdresse($user->getAdressePostal());

            $this->em->persist($commande);

            foreach ($commandeSession as $commandeDetails) {
                if ($restaurant === '') {
                    $platCommandeDetails = $commandeDetails->getPlats();
                    $restaurant = $platCommandeDetails->getRestaurant();
                    $restaurateur = $restaurant->getMembres();

                    $restaurateur = $userRepository->findBy(['id' => $restaurateur->getId()]);
                }
                $bill += $commandeDetails->getPrix();
                $commandeDetails = $commandeDetails->setCommande($commande);

                $plat = $commandeDetails->getPlats();
                $plat = $plat->getLibelle();
                $platsName[] = $plat;

                $this->em->merge($commandeDetails);
            }

            if ($solde >= $bill) {

                $solde = $solde - $bill;
                $user->setSolde($solde);

                $this->em->flush();

                $this->session->remove('commandeSession');
                $this->session->remove('restaurant');

                $message = (new Swift_Message('Nouvelle Commande'))
                    ->setFrom('valentinmont8@gmail.com')
                    ->setTo($restaurateur[0]->getEmail())
                    ->setBody('Bonjour ' . $restaurateur[0]->getNom() . ', Nous vous informons qu\'une nouvelle commande a été passé dans votre restaurant au nom de ' . $user->getNom() . ' à l\'adresse ' . $user->getAdressePostal() . '. L\'heure de livraison est prévue pour : ' . $commande->getDateReception()->format('H:i:s') . ' Les plats commandés sont : ' . implode(', ', $platsName) . '. Pour avoir le detail, nous vous invitons à vous connecter sur la plateforme. L\'équipe Restolink');

                $swift_Mailer->send($message);
                return $this->redirectToRoute('user');
            } else {
                $this->addFlash('danger', 'Oh bah le portefeuille est vide 😭');
                return $this->redirectToRoute('plat_order');
            }
        } else {
            return $this->redirectToRoute('restaurants_liste');
        }
    }


    /**
     * @Route("/delete_plat_order/{id}", name="delete_plat_order")
     */
    public function deletePlatOrder(Plat $plat)
    {
        $commandeSession = $this->session->get('commandeSession');

        if ($commandeSession) {
            foreach ($commandeSession as $commandeDetailsDelete) {
                $commandeDetailsDeleteId = $commandeDetailsDelete->getPlats()->getId();

                if ($commandeDetailsDeleteId === $plat->getId()) {
                    unset($commandeSession[$commandeDetailsDeleteId]);
                    $this->session->set('commandeSession', $commandeSession);
                    $commandeSession = $this->session->get('commandeSession');
                }
            }
            if ($commandeSession) {
                return $this->render('order/index.html.twig', ['commandeSession' => $commandeSession]);
            } else {
                $this->session->remove('commandeSession');
                $this->session->remove('restaurant');
                return $this->redirectToRoute('restaurants_liste');
            }
        }
    }
}
