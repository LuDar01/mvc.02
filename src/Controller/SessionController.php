<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Card\DeckOfCards;

class SessionController extends AbstractController
{
    #[Route('/session', name: 'session_show')]
    public function show(SessionInterface $session): Response
    {
        $deckData = $session->get('deck_data');
        $cardCount = $deckData ? count($deckData) : 0;

        $sessionData = [
            'deck_data_present' => !empty($deckData) ? 'YES - ' . count($deckData) . ' cards' : 'NO',
            'deck_data' => $deckData,
            'card_count' => $cardCount,
            'session_id' => $session->getId(),
            'all_data' => $session->all()
        ];

        return $this->render('session.html.twig', [
            'sessionData' => $sessionData
        ]);
    }

    #[Route('/session/delete', name: 'session_delete')]
    public function delete(SessionInterface $session): Response
    {
        $session->clear();

        // Auto-create a new sorted deck after deletion
        $deck = new DeckOfCards();
        $deck->saveToSession($session);

        $this->addFlash('success', 'Sessionen har raderats och en ny sorterad kortlek har skapats!');

        return $this->redirectToRoute('session_show');
    }
}
