<?php

namespace App\Controller;

use App\Card\BlackjackGame;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProjController extends AbstractController
{
    #[Route('/proj', name: 'proj_home')]
    public function home(): Response
    {
        return $this->render('proj/index.html.twig');
    }

    #[Route('/proj/about', name: 'proj_about')]
    public function about(): Response
    {
        return $this->render('proj/about.html.twig');
    }

    #[Route("/proj/game", name: "proj_game")]
    public function game(SessionInterface $session): Response
    {
        $game = $session->get('blackjack_game');

        if (!$game instanceof BlackjackGame) {
            $game = new BlackjackGame();
            $game->initialDeal();
            $session->set('blackjack_game', $game);
        }

        return $this->render('proj/game.html.twig', [
            'game' => $game
        ]);
    }

    #[Route("/proj/game/hit", name: "proj_game_hit", methods: ['POST'])]
    public function hit(SessionInterface $session): Response
    {
        $game = $session->get('blackjack_game');

        if ($game instanceof BlackjackGame) {
            $game->playerHit();
            $session->set('blackjack_game', $game);
        }

        return $this->redirectToRoute('proj_game');
    }

    #[Route("/proj/game/stand", name: "proj_game_stand", methods: ['POST'])]
    public function stand(SessionInterface $session): Response
    {
        $game = $session->get('blackjack_game');

        if ($game instanceof BlackjackGame) {
            $game->playerStand();
            $session->set('blackjack_game', $game);
        }

        return $this->redirectToRoute('proj_game');
    }


    #[Route("/proj/game/reset", name: "proj_game_reset", methods: ['GET', 'POST'])]
    public function reset(SessionInterface $session): Response
    {
        $session->remove('blackjack_game');
        return $this->redirectToRoute('proj_game');
    }
}
