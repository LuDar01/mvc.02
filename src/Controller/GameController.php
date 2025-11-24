<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Card\Game;

class GameController extends AbstractController
{
    #[Route("/game", name: "game_home")]
    public function home(): Response
    {
        return $this->render('game/home.twig');
    }

    #[Route("/game/doc", name: "game_doc")]
    public function doc(): Response
    {
        return $this->render('game/doc.twig');
    }

    #[Route("/game/play", name: "game_play")]
    public function play(SessionInterface $session): Response
    {
        $game = $session->get('game');
        
        if (!$game) {
            return $this->redirectToRoute('game_start');
        }
        
        return $this->render('game/play.twig', [
            'game' => $game
        ]);
    }

    #[Route("/game/start", name: "game_start")]
    public function start(SessionInterface $session): Response
    {
        $game = new Game();
        $session->set('game', $game);
        
        return $this->redirectToRoute('game_play');
    }

    #[Route("/game/draw", name: "game_draw")]
    public function draw(SessionInterface $session): Response
    {
        $game = $session->get('game');
        
        if (!$game) {
            return $this->redirectToRoute('game_start');
        }

        $game->playerDraw();
        $session->set('game', $game);
        
        return $this->redirectToRoute('game_play');
    }

    #[Route("/game/stand", name: "game_stand")]
    public function stand(SessionInterface $session): Response
    {
        $game = $session->get('game');
        
        if (!$game) {
            return $this->redirectToRoute('game_start');
        }

        $game->playerStand();
        $session->set('game', $game);
        
        return $this->redirectToRoute('game_play');
    }

    #[Route("/game/reset", name: "game_reset")]
    public function reset(SessionInterface $session): Response
    {
        $session->remove('game');
        return $this->redirectToRoute('game_start');
    }

    #[Route("/api/game", name: "api_game")]
    public function apiGame(SessionInterface $session): JsonResponse
    {
        $game = $session->get('game');
        
        if (!$game) {
            return $this->json([
                'game_state' => 'not_started',
                'message' => 'No active game'
            ]);
        }
        
        return $this->json($game->toArray());
    }
}
