<?php

namespace App\Tests\Card;

use App\Card\Card;
use PHPUnit\Framework\TestCase;

/**
 * Test case for class Card.
 */
class CardTest extends TestCase
{
    /**
     * Test creating a Card object and checking its properties.
     */
    public function testCreateCardObject()
    {
        $card = new Card('hearts', 'Q');
        $this->assertInstanceOf('\App\Card\Card', $card);
        $this->assertEquals('hearts', $card->getSuit());
        $this->assertEquals('Q', $card->getValueAsString());
    }

    /**
     * Test getValue for numeric cards (e.g., '5').
     */
    public function testGetNumericValue()
    {
        $card = new Card('spades', '5');
        $this->assertEquals(5, $card->getValue());
    }

    /**
     * Test getValue for face cards (J, Q, K).
     */
    public function testGetFaceCardValue()
    {
        $cardJ = new Card('clubs', 'J');
        $cardQ = new Card('clubs', 'Q');
        $cardK = new Card('clubs', 'K');
        $this->assertEquals(11, $cardJ->getValue());
        $this->assertEquals(12, $cardQ->getValue());
        $this->assertEquals(13, $cardK->getValue());
    }

    /**
     * Test getValue for Ace ('A').
     */
    public function testGetAceValue()
    {
        $cardA = new Card('diamonds', 'A');
        $this->assertEquals(1, $cardA->getValue());
    }

    /**
     * Test getValue for unknown values.
     */
    public function testGetUnknownValue()
    {
        $card = new Card('clubs', 'X');
        $this->assertEquals(0, $card->getValue());
    }

    /**
     * Test getAsString() method.
     */
    public function testGetAsString()
    {
        $card = new Card('hearts', '10');
        $this->assertEquals("[10 of hearts]", $card->getAsString());
    }

    /**
     * Test the roll method.
     */
    public function testRoll()
    {
        $card = new Card('spades', '2');
        $result = $card->roll();
        // Assert that the result is between 1 and 6, which roll() does, even though a card class shouldn't have a roll method.
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
        $this->assertLessThanOrEqual(6, $result);
    }
}
