<?php

namespace App\Card;

class BlackjackGame
{
    private DeckOfCards $deck;
    private CardHand $playerHand;
    private CardHand $dealerHand;
    private string $status; // 'playing', 'player_bust', 'dealer_bust', 'player_win', 'dealer_win', 'push'

    public function __construct()
    {
        $this->deck = new DeckOfCards(CardStyle::Graphic);
        $this->deck->shuffle();
        $this->playerHand = new CardHand();
        $this->dealerHand = new CardHand();
        $this->status = 'playing';
    }

    public function initialDeal(): void
    {
        $this->playerHand->add($this->deck->draw(1)[0]);
        $this->dealerHand->add($this->deck->draw(1)[0]);
        $this->playerHand->add($this->deck->draw(1)[0]);
        $this->dealerHand->add($this->deck->draw(1)[0]);
    }

    public function playerHit(): void
    {
        if ($this->status !== 'playing') {
            return;
        }

        $this->playerHand->add($this->deck->draw(1)[0]);

        if ($this->calculateScore($this->playerHand) > 21) {
            $this->status = 'player_bust';
        }
    }

    public function playerStand(): void
    {
        if ($this->status !== 'playing') {
            return;
        }

        $this->dealerPlay();
    }

    private function dealerPlay(): void
    {
        // Dealern måste dra till minst 17
        while ($this->calculateScore($this->dealerHand) < 17) {
            $this->dealerHand->add($this->deck->draw(1)[0]);
        }

        $this->resolveWinner();
    }

    private function resolveWinner(): void
    {
        $pScore = $this->calculateScore($this->playerHand);
        $dScore = $this->calculateScore($this->dealerHand);

        if ($dScore > 21) {
            $this->status = 'dealer_bust';
        } elseif ($pScore > $dScore) {
            $this->status = 'player_win';
        } elseif ($dScore > $pScore) {
            $this->status = 'dealer_win';
        } else {
            $this->status = 'push';
        }
    }

    public function calculateScore(CardHand $hand): int
    {
        $score = 0;
        $aces = 0;

        foreach ($hand->getCards() as $card) {
            $val = $card->getValue();
            if ($val === 1) { // Ess
                $aces++;
                $score += 11;
            } elseif ($val >= 10) { // J, Q, K
                $score += 10;
            } else {
                $score += $val;
            }
        }

        // Justera Ess från 11 till 1 om man går över 21
        while ($score > 21 && $aces > 0) {
            $score -= 10;
            $aces--;
        }

        return $score;
    }

    // Getters för templaten
    public function getPlayerHand(): CardHand
    {
        return $this->playerHand;
    }
    public function getDealerHand(): CardHand
    {
        return $this->dealerHand;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
}
