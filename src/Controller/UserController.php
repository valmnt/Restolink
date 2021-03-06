<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditUserType;
use App\Form\WalletType;
use App\Repository\PlatRepository;
use App\Repository\RestaurantRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * @Route("/user")
 */
class UserController extends AbstractBaseController
{
    private $entityManagerInterface;
    private $userPasswordEncoderInterface;

    public function __construct(EntityManagerInterface $entityManagerInterface, UserPasswordEncoderInterface $userPasswordEncoderInterface)
    {
        $this->entityManagerInterface = $entityManagerInterface;
        $this->userPasswordEncoderInterface = $userPasswordEncoderInterface;
    }

    /**
     * @Route("/{id}", name="user")
     */
    public function index(PlatRepository $platRepository, RestaurantRepository $restaurantRepository, User $user)
    {
        return $this->getHistoricUserCommandes($platRepository, $restaurantRepository, 'user/index.html.twig', 'user/index.html.twig', $user);
    }

    /**
     * @Route("/edit/{id}", name="edit_user")
     */
    public function editUser(Request $request, User $user)
    {
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManagerInterface->flush();
            return $this->redirectToRoute('user', array('id' => $user->getId()));
        }

        return $this->render('user/edit.html.twig',  ['form' => $form->createView(), 'user' => $user]);
    }

    /**
     * @Route("/alert_delete/{id}", name="alert_delete_user")
     */
    public function alertDelete(User $user)
    {
        return $this->render('user/delete.html.twig', ['user' => $user]);
    }

    // faire un listenener ou Subscriber pour delete les photos sur le serveur
    /**
     * @Route("/delete/{id}", name="delete_user")
     */
    public function deleteUser(User $user)
    {
        if ($user) {
            $this->entityManagerInterface->remove($user);
            $this->entityManagerInterface->flush();
            return $this->render('restaurant/index.html.twig');
        }
    }

    /**
     * @Route("/update_wallet/{id}", name="update_wallet")
     */
    public function updateWallet(Request $request)
    {
        $userForm = new User();
        $form = $this->createForm(WalletType::class, $userForm);
        $form->handleRequest($request);

        $userVerificator = new User();
        $userVerificator = $this->getUser();

        $userForm = $form->getData();
        $formPassword = $userForm->getPassword();
        $passwordValid = $this->userPasswordEncoderInterface->isPasswordValid($userVerificator, $formPassword);

        if ($passwordValid) {
            $solde = $userForm->getSolde() + $userVerificator->getSolde();
            $userVerificator->setSolde($solde);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManagerInterface->flush();
                return $this->redirectToRoute('user', array('id' => $userVerificator->getId()));
            }
        } else if (!$passwordValid && $formPassword !== '') {
            $this->addFlash('danger', 'Mot de passe incorrect 😭');
        }

        return $this->render('user/wallet.html.twig', ['form' => $form->createView(), 'user' => $userVerificator]);
    }
}
