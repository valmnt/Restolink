<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Plat;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Form\PlatType;
use App\Form\RestaurantType;
use App\Repository\PlatRepository;
use App\Repository\RestaurantRepository;
use App\Utils\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/mes_restaurants")
 */
class RestaurateurController extends AbstractController
{

    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/", name="mes_restaurants")
     */
    public function index()
    {
        $user = $this->getUser();

        return $this->render('restaurateur/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/add_restaurant", name="add_restaurant")
     */
    public function addRestaurants(Request $request)
    {
        $restaurant = new Restaurant();
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);
        $user = new User();
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $restaurant->setMembres($user);
            $this->em->persist($restaurant);
            $this->em->flush();

            return $this->redirectToRoute('mes_restaurants');
        }

        return $this->render('restaurateur/add-restaurant.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit_restaurant", name="edit_restaurant_restaurateur")
     */
    public function editRestaurants(Restaurant $restaurant, Request $request)
    {
        $user = $this->getUser();
        if ($this->isRestaurantRestaurateur($user, $restaurant)) {
            $form = $this->createForm(RestaurantType::class, $restaurant);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->em->flush();
                return $this->redirectToRoute('mes_restaurants');
            }

            return $this->render('restaurateur/edit-restaurant.html.twig', [
                'form' => $form->createView()
            ]);
        }
        return new Response("Vous n'êtes pas autorisé", 403);
    }

    /**
     * @Route("/{id}/plats", name="plats_restaurant_restaurateur")
     */
    public function getUnique(Restaurant $restaurant)
    {
        $user = $this->getUser();
        if ($this->isRestaurantRestaurateur($user, $restaurant)) {
            return $this->render(
                'restaurateur/restaurant.html.twig',
                [
                    'user' => $user,
                    'restaurant' => $restaurant,
                ]
            );
        }
        return new Response("Vous n'êtes pas autorisé", 403);
    }

    /**
     * @Route("/{id}/plats/delete/{plat_id}", name="delete_plat_restaurateur")
     * @ParamConverter("plat", options={"id" = "plat_id"})
     */
    public function deletePlat(Restaurant $restaurant, Plat $plat)
    {
        $user = $this->getUser();
        if ($this->isRestaurantRestaurateur($user, $restaurant)) {
            $this->em->remove($plat);
            $this->em->flush();
            return $this->redirectToRoute('plats_restaurant_restaurateur', ['id' => $restaurant->getId()]);
        }
        return new Response("Vous n'êtes pas autorisé", 403);
    }

    /**
     * @Route("/{id}/delete", name="delete_restaurant_restaurateur")
     */
    public function deleteRestaurant(Restaurant $restaurant)
    {
        $user = $this->getUser();
        if ($this->isRestaurantRestaurateur($user, $restaurant)) {
            $this->em->remove($restaurant);
            $this->em->flush();
            return $this->redirectToRoute('mes_restaurants');
        }
        return new Response("Vous n'êtes pas autorisé", 403);
    }

    /**
     * @Route("/{id}/plats/add", name="add_plat_restaurateur")
     */
    public function addPlat(Restaurant $restaurant, Request $request)
    {
        $user = $this->getUser();

        if ($this->isRestaurantRestaurateur($user, $restaurant)) {
            $plat = new Plat();
            $form = $this->createForm(PlatType::class, $plat);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $plat->setRestaurant($restaurant);
                $this->em->persist($plat);
                $this->em->flush();
                return $this->redirectToRoute('plats_restaurant_restaurateur', ['id' => $restaurant->getId()]);
            }

            return $this->render('restaurateur/edit-plat.html.twig', [
                'form' => $form->createView()
            ]);
        }
        return new Response("Vous n'êtes pas autorisé", 403);
    }

    /**
     * @Route("/{id}/plats/edit/{plat_id}", name="edit_plat_restaurateur")
     * @ParamConverter("plat", options={"id" = "plat_id"})
     */
    public function editPlat(Restaurant $restaurant, Plat $plat, Request $request)
    {
        $user = $this->getUser();
        if ($this->isRestaurantRestaurateur($user, $restaurant)) {
            $form = $this->createForm(PlatType::class, $plat);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->em->flush();
                return $this->redirectToRoute('plats_restaurant_restaurateur', ['id' => $restaurant->getId()]);
            }

            return $this->render('restaurateur/edit-plat.html.twig', [
                'form' => $form->createView()
            ]);
        }
        return new Response("Vous n'êtes pas autorisé", 403);
    }

    /**
     * A implémenter dans un voter
     * https://symfony.com/doc/current/security/voters.html
     */
    private function isRestaurantRestaurateur(User $user, Restaurant $restaurant)
    {
        $restaurants =  $user->getRestaurants();
        if ($restaurants->contains($restaurant)) {
            return true;
        }
        return false;
    }

    /**
     * @Route("/valid-commande/{id}", name="valid-command")
     */
    public function validcommand(Restaurant $restaurant, PlatRepository $platRepository, RestaurantRepository $restaurantRepository)
    {
        $commandesID = [];
        $commandeMembers = [];
        $commandeStatus = [];
        $arrayAllCommandeDetails = [];
        $commandeDetails = [];
        $commandes = $restaurant->getCommandes();

        foreach ($commandes as $commande) {
            $commandeDetailsLocal = $commande->getCommandeDetails();
            $commandeStatus[] = $commande->getStatus();
            $commandesID[] = $commande->getId();
            $commandeMembers[] = $commande->getMembres()->getNom();
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

        return $this->render('restaurateur/valid-command.html.twig', [
            'commandeDetails' => $arrayAllCommandeDetails,
            'restaurant' => $commandeDetailRestaurant,
            'status' => $commandeStatus,
            'commandesID' => $commandesID,
            'commandeMembers' => $commandeMembers
        ]);
    }

    /**
     * @Route("/update-status/{id}", name="update-status")
     */
    public function updateStatusCommande(Commande $commande)
    {
        $commande->setStatus(1);
        $this->em->flush();
        return $this->redirectToRoute('mes_restaurants');
    }
}
