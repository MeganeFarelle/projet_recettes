<?php

namespace App\Controller;

use App\Form\ContactFormType;
use App\Service\SendMailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, SendMailService $mailService): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            // ENVOI DE L’EMAIL À L’ADMIN
            $mailService->send(
                'no-reply@monsite.net',
                'admin@test.com',       
                'Nouveau message de contact',
                'contact',                 
                [
                    'nom' => $data['nom'],
                    'contactEmail' => $data['email'],   // ✔ FIX
                    'message' => $data['message']
                ]
            );

            $this->addFlash('success', 'Votre message a été envoyé avec succès !');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/contact.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}
