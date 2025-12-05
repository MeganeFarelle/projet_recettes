<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class PostController extends AbstractController
{
    // -------------------------------------------------------------
    // 1) Appel API simple (non sécurisée)
    // -------------------------------------------------------------
    #[Route('/posts', name: 'app_posts')]
    public function index(HttpClientInterface $httpClient): Response
    {
        $response = $httpClient->request(
            'GET',
            'https://jsonplaceholder.typicode.com/posts'
        );

        $posts = $response->toArray();

        return $this->render('posts/index.html.twig', [
            'posts' => $posts
        ]);
    }

    // -------------------------------------------------------------
    // 2) Appel API sécurisée (JWT Platzi)
    // -------------------------------------------------------------
    #[Route('/post_with_token', name: 'app_posts_token')]
    public function indexWithToken(HttpClientInterface $httpClient): Response
    {
        // 1) Login pour obtenir un token
        $login = $httpClient->request(
            'POST',
            'https://api.escuelajs.co/api/v1/auth/login',
            [
                'json' => [
                    'email' => 'john@mail.com',
                    'password' => 'changeme'
                ]
            ]
        );

        $data = $login->toArray();
        $token = $data['access_token'];

        // 2) Appel sécurisé avec Bearer token
        $response = $httpClient->request(
            'GET',
            'https://api.escuelajs.co/api/v1/products',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]
        );

        $products = $response->toArray();

        return $this->render('posts/token.html.twig', [
            'posts' => $products
        ]);
    }


    // -------------------------------------------------------------
    // 3) Version LENTE (simulateur API lente)
    // -------------------------------------------------------------
    #[Route('/cached_posts', name: 'app_cached_posts')]
    public function cachedPosts(HttpClientInterface $httpClient): Response
    {
        sleep(3); // Simule une API très lente

        $response = $httpClient->request(
            'GET',
            'https://jsonplaceholder.typicode.com/posts'
        );

        $posts = $response->toArray();

        return $this->render('posts/index.html.twig', [
            'posts' => $posts
        ]);
    }


    // -------------------------------------------------------------
    // 4) Version RAPIDE GRÂCE AU CACHE
    // -------------------------------------------------------------
    #[Route('/cached_posts_fast', name: 'app_cached_posts_fast')]
    public function cachedPostsFast(HttpClientInterface $httpClient, CacheInterface $cache): Response
    {
        $posts = $cache->get('external_posts_cache', function (ItemInterface $item) use ($httpClient) {

            // Le cache expire après 20 secondes
            $item->expiresAfter(20);

            // SIMULATION d'une API lente (3 secondes)
            sleep(3);

            // Requête API réelle
            $response = $httpClient->request(
                'GET',
                'https://jsonplaceholder.typicode.com/posts'
            );

            return $response->toArray();
        });

        return $this->render('posts/index.html.twig', [
            'posts' => $posts
        ]);
    }
}
