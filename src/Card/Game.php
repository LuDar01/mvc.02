<?php

namespace App\Card;

class Game
{
    private DeckOfCards $deck;
    private CardHand $player;
    private CardHand $bank;
    private string $state;

    public function __construct()
    {
        $this->deck = new DeckOfCards(true);
        $this->deck->shuffle();
        $this->player = new CardHand();
        $this->bank = new CardHand();
        $this->state = 'player_turn';
    }

    public function start(): void
    {
        $this->deck = new DeckOfCards(true);
        $this->deck->shuffle();
        $this->player = new CardHand();
        $this->bank = new CardHand();
        $this->state = 'player_turn';
    }

    /**
     * @return Card|null The drawn card, or null if the deck is empty.
     */
    public function playerDraw(): ?Card
    {
        $cards = $this->deck->draw(1);

        if (empty($cards)) {
            return null; // Deck is empty
        }

        $card = $cards[0];
        $this->player->add($card);

        if ($this->calculateScore($this->player) > 21) {
            $this->state = 'bank_wins';
        }

        return $card;
    }

    public function playerStand(): void
    {
        $this->state = 'bank_turn';
        $this->bankPlay();
    }

    private function bankPlay(): void
    {
        $bankScore = $this->calculateScore($this->bank);

        while ($bankScore < 17) {
            $cards = $this->deck->draw(1);
            if (empty($cards)) {
                break;
            }
            $this->bank->add($cards[0]);
            $bankScore = $this->calculateScore($this->bank);
        }

        $this->determineWinner();
    }

    private function determineWinner(): void
    {
        $playerScore = $this->calculateScore($this->player);
        $bankScore = $this->calculateScore($this->bank);

        if ($playerScore > 21) {
            $this->state = 'bank_wins';
        } elseif ($bankScore > 21) {
            $this->state = 'player_wins';
        } elseif ($playerScore > $bankScore) {
            $this->state = 'player_wins';
        } elseif ($bankScore > $playerScore) {
            $this->state = 'bank_wins';
        } else {
            $this->state = 'draw';
        }
    }

    public function calculateScore(CardHand $hand): int
    {
        $score = 0;
        $aces = 0;

        foreach ($hand->getCards() as $card) {
            $value = $card->getValue();
            if ($value == 1) {
                $aces++;
                $score += 11;
            } elseif ($value > 10) {
                $score += 10;
            } else {
                $score += $value;
            }
        }

        while ($score > 21 && $aces > 0) {
            $score -= 10;
            $aces--;
        }

        return $score;
    }

    public function getPlayer(): CardHand
    {
        return $this->player;
    }

    public function getBank(): CardHand
    {
        return $this->bank;
    }

    public function getDeck(): DeckOfCards
    {
        return $this->deck;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getPlayerScore(): int
    {
        return $this->calculateScore($this->player);
    }

    public function getBankScore(): int
    {
        return $this->calculateScore($this->bank);
    }

    public function toArray(): array
    {
        return [
            'player_cards' => $this->player->getCards(),
            'player_score' => $this->getPlayerScore(),
            'bank_cards' => $this->bank->getCards(),
            'bank_score' => $this->getBankScore(),
            'game_state' => $this->state,
            'deck_count' => $this->deck->count()
        ];
    }
}
