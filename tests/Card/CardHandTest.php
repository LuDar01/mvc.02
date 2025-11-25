<?php

namespace App\Tests\Card;

use App\Card\Card;
use App\Card\CardHand;
use PHPUnit\Framework\TestCase;

/**
 * Test case for class CardHand.
 */
class CardHandTest extends TestCase
{
    /**
     * Test creating a CardHand object.
     */
    public function testCreateCardHandObject()
    {
        $hand = new CardHand();
        $this->assertInstanceOf('\App\Card\CardHand', $hand);
    }

    /**
     * Test adding cards and counting them.
     */
    public function testAddAndCountCards()
    {
        $hand = new CardHand();
        $card1 = new Card('hearts', 'A');
        $card2 = new Card('spades', 'K');

        $this->assertEquals(0, $hand->count());

        $hand->add($card1);
        $this->assertEquals(1, $hand->count());

        $hand->add($card2);
        $this->assertEquals(2, $hand->count());
    }

    /**
     * Test retrieving all cards.
     */
    public function testGetCards()
    {
        $hand = new CardHand();
        $card1 = new Card('hearts', 'A');
        $card2 = new Card('spades', 'K');

        $hand->add($card1);
        $hand->add($card2);

        $cards = $hand->getCards();
        $this->assertIsArray($cards);
        $this->assertCount(2, $cards);
        $this->assertInstanceOf('\App\Card\Card', $cards[0]);
    }
}
