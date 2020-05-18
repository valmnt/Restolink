<?php

namespace App\Controller\Admin;

use App\Entity\Plat;
use App\Entity\Restaurant;
use App\Form\PlatType;
use App\Form\RestaurantType;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/restaurant")
 */
class RestaurantAdminController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/", name="restaurant_index", methods={"GET"})
     */
    public function index(RestaurantRepository $restaurantRepository): Response
    {
        return $this->render('admin/restaurant/index.html.twig', [
            'restaurants' => $restaurantRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="restaurant_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $restaurant = new Restaurant();
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($restaurant);
            $entityManager->flush();

            return $this->redirectToRoute('admin_restaurant_index');
        }

        return $this->render('admin/restaurant/new.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="restaurant_show", methods={"GET"})
     */
    public function show(Restaurant $restaurant): Response
    {
        return $this->render('admin/restaurant/show.html.twig', [
            'restaurant' => $restaurant,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="restaurant_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Restaurant $restaurant): Response
    {
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_restaurant_index');
        }

        return $this->render('admin/restaurant/edit.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/plats/delete/{plat_id}", name="delete_plat_restaurant")
     * @ParamConverter("plat", options={"id" = "plat_id"})
     */
    public function deletePlat(Restaurant $restaurant, Plat $plat)
    {
        $this->em->remove($plat);
        $this->em->flush();
        return $this->redirectToRoute('admin_restaurant_show', ['id' => $restaurant->getId()]);
    }

    /**
     * @Route("/{id}/plats/add", name="add_plat_restaurateur")
     */
    public function addPlat(Restaurant $restaurant, Request $request)
    {
        $plat = new Plat();
        $form = $this->createForm(PlatType::class, $plat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plat->setRestaurant($restaurant);
            $this->em->persist($plat);
            $this->em->flush();
            return $this->redirectToRoute('admin_restaurant_show', ['id' => $restaurant->getId()]);
        }

        return $this->render('admin/restaurant/edit-plat.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name="delete_restaurant")
     */
    public function deleteRestaurant(Restaurant $restaurant)
    {
        $this->em->remove($restaurant);
        $this->em->flush();
        return $this->redirectToRoute('admin_restaurant_index');
    }

    /**
     * @Route("/{id}/plats/edit/{plat_id}", name="edit_plat_restaurant")
     * @ParamConverter("plat", options={"id" = "plat_id"})
     */
    public function editPlat(Restaurant $restaurant,Plat $plat, Request $request)
    {
        $form = $this->createForm(PlatType::class, $plat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            return $this->redirectToRoute('admin_restaurant_show', ['id' => $restaurant->getId()]);
        }

        return $this->render('admin/restaurant/edit-plat.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
