<?php

namespace App\Controller\Admin;

use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ContactAdminController extends AbstractController
{
    /**
     * @Route("/messages", name="messages")
     */
    public function index(ContactRepository $contactRepository)
    {
        $contacts = $contactRepository->findAll();
        $nbContacts = count($contacts);
        return $this->render('admin/contact/index.html.twig', [
            'contacts' => $contacts,
            'nbContact' => $nbContacts
        ]);
    }
}
