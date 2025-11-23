<?php
namespace App\Card;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DeckOfCards
{
    private array $cards = [];

    public function __construct(bool $useGraphics = true)
    {
        $suits = ['hearts', 'spades', 'clubs', 'diamonds'];
        $values = ['2','3','4','5','6','7','8','9','10','J','Q','K','A'];

        foreach ($suits as $suit) {
            foreach ($values as $value) {
                $this->cards[] = $useGraphics ? new CardGraphic($suit, $value) : new Card($suit, $value);
            }
        }
    }

    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    public function draw(int $num = 1): array
    {
        return array_splice($this->cards, 0, $num);
    }

    public function getCards(): array
    {
        return $this->cards;
    }

    public function count(): int
    {
        return count($this->cards);
    }

    public function saveToSession(SessionInterface $session): void
    {
        // Store deck as serialized data
        $deckData = [];
        foreach ($this->cards as $card) {
            $deckData[] = [
                'suit' => $card->getSuit(),
                'value' => $card->getValueAsString(),
                'type' => $card instanceof CardGraphic ? 'graphic' : 'basic'
            ];
        }
        $session->set('deck_data', $deckData);
    }

    public static function loadFromSession(SessionInterface $session): self
    {
        $deckData = $session->get('deck_data');
        
        if (!$deckData) {
            // Create new deck AND save it to session immediately
            $deck = new self();
            $deck->saveToSession($session);
            return $deck;
        }

        $deck = new self();
        $deck->cards = [];
        
        foreach ($deckData as $cardData) {
            if ($cardData['type'] === 'graphic') {
                $deck->cards[] = new CardGraphic($cardData['suit'], $cardData['value']);
            } else {
                $deck->cards[] = new Card($cardData['suit'], $cardData['value']);
            }
        }
        
        return $deck;
    }
}