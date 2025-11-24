<?php

namespace App\Card;

class CardGraphic extends Card
{
    public function getUnicode(): string
    {
        $suits = ['hearts' => '♥', 'spades' => '♠', 'clubs' => '♣', 'diamonds' => '♦'];
        return $this->getValueAsString() . $suits[$this->getSuit()];
    }

    public function getSuitSymbol(): string
    {
        $suits = ['hearts' => '♥', 'spades' => '♠', 'clubs' => '♣', 'diamonds' => '♦'];
        return $suits[$this->getSuit()];
    }

    public function getSuitClass(): string
    {
        return $this->getSuit();
    }
}
