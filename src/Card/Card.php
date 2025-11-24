<?php

namespace App\Card;

class Card
{
    private string $suit;
    private string $value;

    public function __construct(string $suit, string $value)
    {
        $this->value = $value;
        $this->suit = $suit;
    }

    public function roll(): int
    {
        $this->value = (string)random_int(1, 6);
        return (int)$this->value;
    }

    public function getSuit(): string
    {
        return $this->suit;
    }

    public function getValue(): int
    {
        if (is_numeric($this->value)) {
            return (int)$this->value;
        }

        switch ($this->value) {
            case 'J': return 11;
            case 'Q': return 12;
            case 'K': return 13;
            case 'A': return 1;
            default: return 0;
        }
    }

    public function getValueAsString(): string
    {
        return $this->value;
    }

    public function getAsString(): string
    {
        return "[{$this->value} of {$this->suit}]";
    }
}
