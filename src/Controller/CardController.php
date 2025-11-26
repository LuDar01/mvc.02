<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Card\DeckOfCards;

class CardController extends AbstractController
{
    private function loadDeck(SessionInterface $session): DeckOfCards
    {
        $deck = $session->get('deck');
        if (!$deck instanceof DeckOfCards) {
            $deck = new DeckOfCards();
            $deck->shuffle();
            $deck->saveToSession($session);
        }
        return $deck;
    }

    #[Route('/card', name: 'card_home')]
    public function home(SessionInterface $session): Response
    {
        $this->loadDeck($session);
        return $this->render('card/index.twig');
    }

    #[Route('/card/deck', name: 'card_deck')]
    public function deck(SessionInterface $session): Response
    {
        $deck = $this->loadDeck($session);
        return $this->render('card/deck.twig', [
            'cards' => $deck->getCards(),
            'count' => $deck->count()
        ]);
    }

    #[Route('/card/deck/shuffle', name: 'card_shuffle')]
    public function shuffle(SessionInterface $session): Response
    {
        $deck = new DeckOfCards();
        $deck->shuffle();
        $deck->saveToSession($session);

        return $this->render('card/shuffle.twig', [
            'cards' => $deck->getCards()
        ]);
    }

    #[Route('/card/deck/draw', name: 'card_draw_one')]
    public function drawOne(SessionInterface $session): Response
    {
        $deck = $this->loadDeck($session);

        $cards = $deck->draw(1);
        $deck->saveToSession($session);

        return $this->render('card/draw.twig', [
            'cards' => $cards,
            'remaining' => $deck->count()
        ]);
    }

    #[Route('/card/deck/draw/{num<\d+>}', name: 'card_draw_many')]
    public function drawMany(int $num, SessionInterface $session): Response
    {
        $deck = $this->loadDeck($session);

        $cards = $deck->draw($num);
        $deck->saveToSession($session);

        return $this->render('card/draw.twig', [
            'cards' => $cards,
            'remaining' => $deck->count()
        ]);
    }
}
