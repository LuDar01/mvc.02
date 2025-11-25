<?php

namespace App\Card;

/**
 * Class Card represents a single playing card with a suit and a value.
 */
class Card
{
    private string $suit;
    private string $value;

    /**
     * Card constructor.
     * * @param string $suit The suit of the card (e.g., 'hearts', 'spades').
     * @param string $value The value of the card (e.g., '2', 'K', 'A').
     */
    public function __construct(string $suit, string $value)
    {
        $this->value = $value;
        $this->suit = $suit;
    }

    /**
     * Rolls a random number (1-6) and sets it as the card's value.
     * NOTE: This method seems misplaced in a Card class (more for Dice), but is included for coverage.
     * * @return int The newly set integer value (1-6).
     */
    public function roll(): int
    {
        $this->value = (string)random_int(1, 6);
        return (int)$this->value;
    }

    /**
     * Gets the suit of the card.
     * * @return string The suit.
     */
    public function getSuit(): string
    {
        return $this->suit;
    }

    /**
     * Gets the integer value of the card, converting face cards and Ace.
     * J=11, Q=12, K=13, A=1. Numeric cards return their number.
     * * @return int The numeric value of the card.
     */
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

    /**
     * Gets the string representation of the card's value (e.g., 'A', '10', 'Q').
     * * @return string The string value.
     */
    public function getValueAsString(): string
    {
        return $this->value;
    }

    /**
     * Gets a formatted string representation of the card.
     * * @return string A string like "[Value of Suit]".
     */
    public function getAsString(): string
    {
        return "[{$this->value} of {$this->suit}]";
    }
}
