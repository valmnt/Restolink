<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
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
    public function editUser(Request $request, User $user, EntityManagerInterface $entityManagerInterface)
    {
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManagerInterface->flush();
            return $this->redirectToRoute('user');
        }

        return $this->render('user/edit.html.twig',  ['form' => $form->createView()]);
    }
}