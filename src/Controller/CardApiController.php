<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Card\DeckOfCards;

class CardApiController extends AbstractController
{
    #[Route('/api/deck', name: 'api_deck', methods: ['GET'])]
    public function getDeck(SessionInterface $session): JsonResponse
    {
        $deck = DeckOfCards::loadFromSession($session);

        $cardsData = [];
        foreach ($deck->getCards() as $card) {
            $cardsData[] = [
                'suit' => $card->getSuit(),
                'value' => $card->getValueAsString(),
                'suit_symbol' => $card->getSuitSymbol(),
                'unicode' => $card instanceof \App\Card\CardGraphic ? $card->getUnicode() : ''
            ];
        }

        return $this->json([
            'deck' => $cardsData,
            'remaining' => $deck->count()
        ]);
    }

    #[Route('/api/deck/shuffle', name: 'api_deck_shuffle', methods: ['POST'])]
    public function shuffleDeck(SessionInterface $session): JsonResponse
    {
        $deck = new DeckOfCards();
        $deck->shuffle();
        $deck->saveToSession($session);

        $cardsData = [];
        foreach ($deck->getCards() as $card) {
            $cardsData[] = [
                'suit' => $card->getSuit(),
                'value' => $card->getValueAsString(),
                'suit_symbol' => $card->getSuitSymbol()
            ];
        }

        return $this->json([
            'message' => 'Deck shuffled',
            'deck' => $cardsData,
            'remaining' => $deck->count()
        ]);
    }

    #[Route('/api/deck/draw', name: 'api_deck_draw_one', methods: ['POST'])]
    public function drawOne(SessionInterface $session): JsonResponse
    {
        return $this->drawCards(1, $session);
    }

    #[Route('/api/deck/draw/{number}', name: 'api_deck_draw_many', methods: ['POST'])]
    public function drawMany(int $number, SessionInterface $session): JsonResponse
    {
        return $this->drawCards($number, $session);
    }

    private function drawCards(int $number, SessionInterface $session): JsonResponse
    {
        $deck = DeckOfCards::loadFromSession($session);

        if ($number > $deck->count()) {
            return $this->json([
                'error' => 'Not enough cards left in deck. Only ' . $deck->count() . ' cards remaining.'
            ], 400);
        }

        $drawnCards = $deck->draw($number);
        $deck->saveToSession($session);

        $drawnData = [];
        foreach ($drawnCards as $card) {
            $drawnData[] = [
                'suit' => $card->getSuit(),
                'value' => $card->getValueAsString(),
                'suit_symbol' => $card->getSuitSymbol(),
                'representation' => $card->getAsString()
            ];
        }

        return $this->json([
            'drawn' => $drawnData,
            'drawn_count' => count($drawnData),
            'remaining' => $deck->count()
        ]);
    }
}
