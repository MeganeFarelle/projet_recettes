<?php

namespace App\Controller;

use App\Taxe\CalculatorTaxe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    #[Route('/hello_world', name: 'app_hello_world')]
    public function show_hello_world(): Response
    {
        return $this->render('hello/hello_world.html.twig');
    }

    #[Route('/hello', name: 'app_hello')]
    public function index(CalculatorTaxe $calculator): Response
    {
        $prixHT = 360;
        $tva = $calculator->calculerTVA($prixHT);
        $ttc = $calculator->calculerTTC($prixHT);

        return new Response("
        <h1>Notre service de calcul de taxe fonctionne !</h1>
        <p>Pour un prix de $prixHT € HT :</p>
        <p>TVA = $tva €</p>
        <p>TTC = $ttc €</p>
    ");
    }

    #[Route('/send-test-email', name: 'send_test_email')]
    public function sendTestEmail(\App\Service\SendMailService $mailer): Response
    {
        $mailer->send(
            'no-reply@monsite.net',
            'destinataire@monsite.net',
            'Titre de mon message',
            'test',
            [
                'prenom' => 'Lola',
                'nom' => 'Dupont'
            ]
        );

        return new Response('Email envoyé (regarde dans Mailhog) !');
    }
}
