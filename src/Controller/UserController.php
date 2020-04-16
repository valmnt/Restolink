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

class UserController extends AbstractController
{
    private $entityManagerInterface;

    public function __construct(EntityManagerInterface $entityManagerInterface)
    {
        $this->entityManagerInterface = $entityManagerInterface;
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
    public function updateWallet(Request $request, User $user)
    {
        $form = $this->createForm(WalletType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $user->getPassword();
            $userData = new User();
            $userData = $form->getData();
            $formPassword = $userData->getPassword();

            if ($password === $formPassword) {
                $this->entityManagerInterface->flush();
                $role = $user->getRoles();
                return $this->render('user/index.html.twig', ['user' => $user, 'role' => $role]);
            }
        }

        return $this->render('user/wallet.html.twig', ['form' => $form->createView()]);
    }
}
