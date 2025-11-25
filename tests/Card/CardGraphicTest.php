<?php

namespace App\Tests\Card;

use App\Card\CardGraphic;
use PHPUnit\Framework\TestCase;

/**
 * Test case for class CardGraphic, ensuring all graphic methods work.
 */
class CardGraphicTest extends TestCase
{
    /**
     * Test creating a CardGraphic object.
     */
    public function testCreateCardGraphicObject()
    {
        $card = new CardGraphic('spades', 'K');
        $this->assertInstanceOf('\App\Card\CardGraphic', $card);
    }

    /**
     * Test getUnicode method for different suits.
     */
    public function testGetUnicode()
    {
        $cardHeart = new CardGraphic('hearts', 'A');
        $cardSpade = new CardGraphic('spades', '10');
        $cardClub = new CardGraphic('clubs', '2');
        $cardDiamond = new CardGraphic('diamonds', 'Q');

        $this->assertEquals('A♥', $cardHeart->getUnicode());
        $this->assertEquals('10♠', $cardSpade->getUnicode());
        $this->assertEquals('2♣', $cardClub->getUnicode());
        $this->assertEquals('Q♦', $cardDiamond->getUnicode());
    }

    /**
     * Test getSuitSymbol method.
     */
    public function testGetSuitSymbol()
    {
        $cardHeart = new CardGraphic('hearts', 'A');
        $cardSpade = new CardGraphic('spades', '10');

        $this->assertEquals('♥', $cardHeart->getSuitSymbol());
        $this->assertEquals('♠', $cardSpade->getSuitSymbol());
    }

    /**
     * Test getSuitClass method (returns the suit name as a class).
     */
    public function testGetSuitClass()
    {
        $card = new CardGraphic('diamonds', '5');
        $this->assertEquals('diamonds', $card->getSuitClass());
    }
}
