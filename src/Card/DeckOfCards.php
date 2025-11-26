<?php

namespace App\Card;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DeckOfCards
{
    private array $cards = [];

    public function __construct(CardStyle $style = CardStyle::Graphic)
    {
        $suits = ['hearts', 'spades', 'clubs', 'diamonds'];
        $values = ['2','3','4','5','6','7','8','9','10','J','Q','K','A'];

        foreach ($suits as $suit) {
            foreach ($values as $value) {
                $this->cards[] = match ($style) {
                    CardStyle::Graphic => new CardGraphic($suit, $value),
                    CardStyle::Basic   => new Card($suit, $value),
                };
            }
        }
    }

    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    public function draw(int $num = 1): array
    {
        $drawn = [];
        for ($i = 0; $i < $num; $i++) {
            if (empty($this->cards)) {
                break;
            }

            $card = array_pop($this->cards);
            $drawn[] = $card;
        }
        return $drawn;
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
            $deck = new self();
            $deck->saveToSession($session);
            return $deck;
        }

        $deck = new self(CardStyle::Basic); // overwritten anyway
        $deck->cards = [];

        foreach ($deckData as $cardData) {
            $deck->cards[] = $cardData['type'] === 'graphic'
                ? new CardGraphic($cardData['suit'], $cardData['value'])
                : new Card($cardData['suit'], $cardData['value']);
        }

        return $deck;
    }
}
