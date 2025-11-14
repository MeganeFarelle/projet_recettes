<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;



class LuckyController extends AbstractController
{
    #[Route('/lucky', name: 'app_lucky')]
    public function index(): Response
    {
        return $this->render('lucky/index.html.twig', [
            'controller_name' => 'LuckyController',
        ]);
    }

    #[Route('/lucky/number', name: 'app_lucky_number')]
    public function show_number(): Response
    {
        $number = random_int(0, 100);
        return new Response('Nombre tire au sort: ' . $number);
    }

    #[Route('/lucky/number_for_username', name: 'app_lucky_number_v2')]
    public function show_number_v2(Request $request): Response
    {
        $username = $request->query->get('username', 'inconnu');
        $number = random_int(0, 100);
        $message = "Nombre tiré au sort : $number pour $username";
        return new Response($message);
    }

    #[Route('/lucky/number_v3', name: 'app_lucky_number_v3')]
    public function show_number_v3(Request $request): Response
    {
        for ($i =0; $i<=10; $i ++ ){
            $number = random_int(0, 100);
            $numbers [] = $number;
        }
        
        return $this->render('lucky/number.html.twig', [
        'randomNumber' => $numbers]);
    }

}
