<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RenderTwig extends AbstractController
{
    #[Route("/", name: "me")]
    public function me(): Response
    {
        return $this->render('me.html.twig');
    }

    #[Route("/lucky", name: "lucky_number")]
    public function number(): Response
    {
        return $this->render('lucky_number.html.twig', [
            'number' => random_int(0, 100),
        ]);
    }

    #[Route("/about", name: "about")]
    public function about(): Response
    {
        return $this->render('about.html.twig');
    }

    #[Route("/report", name: "report")]
    public function report(): Response
    {
        return $this->render('report.html.twig');
    }

    #[Route("/api", name: "json_api")]
    public function apiLanding(): Response
    {
        $routes = [
            ['path' => '/api/quote', 'name' => 'Quote of the day', 'description' => 'Random quote', 'method' => 'GET'],
            ['path' => '/api/deck', 'name' => 'Get Deck', 'description' => 'Full sorted deck', 'method' => 'GET'],
            ['path' => '/api/deck/shuffle', 'name' => 'Shuffle Deck', 'description' => 'Shuffles deck', 'method' => 'POST'],
            ['path' => '/api/deck/draw', 'name' => 'Draw Card', 'description' => 'Draw 1 card', 'method' => 'POST'],
            ['path' => '/api/deck/draw/{number}', 'name' => 'Draw Multiple Cards', 'description' => 'Draws {number} cards', 'method' => 'POST'],
            ['path' => '/api/game', 'name' => 'Game Status', 'description' => 'Current game state and scores', 'method' => 'GET'],
        ];

        return $this->render('json_api.html.twig', ['routes' => $routes]);
    }
}
