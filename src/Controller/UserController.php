<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditUserType;
use App\Form\WalletType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    private $entityManagerInterface;
    private $userPasswordEncoderInterface;

    public function __construct(EntityManagerInterface $entityManagerInterface, UserPasswordEncoderInterface $userPasswordEncoderInterface)
    {
        $this->entityManagerInterface = $entityManagerInterface;
        $this->userPasswordEncoderInterface = $userPasswordEncoderInterface;
    }

    /**
     * @Route("/user", name="user")
     */
    public function index()
    {
        $user = $this->getUser();
        $role = $user->getRoles();
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'role' => $role
        ]);
    }

    /**
     * @Route("/edit_user/{id}", name="edit_user")
     */
    public function editUser(Request $request, User $user)
    {
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManagerInterface->flush();
            return $this->redirectToRoute('user');
        }

        return $this->render('user/edit.html.twig',  ['form' => $form->createView()]);
    }

    /**
     * @Route("/alert_delete_user/{id}", name="alert_delete_user")
     */
    public function alertDelete(User $user)
    {
        return $this->render('user/delete.html.twig', ['user' => $user]);
    }

    // faire un listenener ou Subscriber pour delete les photos sur le serveur
    /**
     * @Route("/delete_user/{id}", name="delete_user")
     */
    public function deleteUser(User $user)
    {
        if ($user) {
            $this->entityManagerInterface->remove($user);
            $this->entityManagerInterface->flush();
            return $this->render('index/index.html.twig');
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
            $userVerificator->setSolde($userForm->getSolde());
            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManagerInterface->flush();
                $role = $userVerificator->getRoles();
                return $this->render('user/index.html.twig', ['user' => $userVerificator, 'role' => $role]);
            }
        } else if (!$passwordValid && $formPassword !== '') {
            $this->addFlash('danger', 'Mot de passe incorrect 😭');
        }

        return $this->render('user/wallet.html.twig', ['form' => $form->createView()]);
    }
}
