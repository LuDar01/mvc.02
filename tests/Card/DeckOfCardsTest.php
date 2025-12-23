<?php

namespace App\Tests\Card;

use App\Card\Card;
use App\Card\CardGraphic;
use App\Card\DeckOfCards;
use App\Card\CardStyle; // Added import
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Test case for class DeckOfCards, covering all construction, draw,
 * session handling, and utility methods for 100% coverage.
 */
class DeckOfCardsTest extends TestCase
{
    /**
     * Helper to create a mock session.
     */
    private function getMockSession(): SessionInterface&\PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(SessionInterface::class);
    }

    // --- Constructor and Utilities Tests ---

    /**
     * Test creating a DeckOfCards object using basic cards (useGraphics = false).
     * Covers: __construct(false)
     */
    public function testCreateBasicDeck()
    {
        $deck = new DeckOfCards(CardStyle::Basic); // Changed from false
        $this->assertInstanceOf('\App\Card\DeckOfCards', $deck);
        $this->assertEquals(52, $deck->count());
        $this->assertInstanceOf('\App\Card\Card', $deck->getCards()[0]);
        $this->assertNotInstanceOf('\App\Card\CardGraphic', $deck->getCards()[0]);
    }

    /**
     * Test creating a DeckOfCards object using graphic cards (default).
     * Covers: __construct(true)
     */
    public function testCreateGraphicDeck()
    {
        $deck = new DeckOfCards(CardStyle::Graphic); // Changed from true
        $this->assertInstanceOf('\App\Card\DeckOfCards', $deck);
        $this->assertEquals(52, $deck->count());
        $this->assertInstanceOf('\App\Card\CardGraphic', $deck->getCards()[0]);
    }

    /**
     * Test the shuffle method ensures the deck count remains 52.
     * Covers: shuffle()
     */
    public function testShuffle()
    {
        $deck = new DeckOfCards(CardStyle::Basic); // Changed from false
        $initialCount = $deck->count();
        $deck->shuffle();
        $this->assertEquals($initialCount, $deck->count());
    }

    // --- Draw Method Tests ---

    /**
     * Test the draw method, drawing one card.
     * Covers: draw(1)
     */
    public function testDrawOne()
    {
        $deck = new DeckOfCards(CardStyle::Basic); // Changed from false
        $initialCount = $deck->count();
        $drawn = $deck->draw(1);

        $this->assertCount(1, $drawn);
        $this->assertEquals($initialCount - 1, $deck->count());
    }

    /**
     * Test drawing multiple cards.
     * Covers: draw(5)
     */
    public function testDrawMultiple()
    {
        $deck = new DeckOfCards(CardStyle::Basic); // Changed from false
        $initialCount = $deck->count();
        $drawn = $deck->draw(5);

        $this->assertCount(5, $drawn);
        $this->assertEquals($initialCount - 5, $deck->count());
    }

    /**
     * Test drawing more cards than available (Covers the 'if (empty($this->cards)) { break; }' path).
     * This also implicitly covers the redundant 'if ($card)' check, as array_pop is guaranteed
     * to return a Card object (not null) when the deck is not empty.
     * Covers: draw(>count) and the initial break condition.
     */
    public function testDrawMoreThanAvailable()
    {
        $deck = new DeckOfCards(CardStyle::Basic); // Changed from false
        $drawn = $deck->draw(60); // Try to draw 60 from 52

        $this->assertCount(52, $drawn);
        $this->assertEquals(0, $deck->count());
    }


    // --- Session Handling Tests ---

    /**
     * Test saving the deck to a session with basic cards.
     * Covers: saveToSession() - 'basic' type path.
     */
    public function testSaveToSessionBasicCard()
    {
        $deck = new DeckOfCards(CardStyle::Basic); // Changed from false
        $session = $this->getMockSession();

        $session->expects($this->once())
            ->method('set')
            ->with('deck_data', $this->callback(function ($data) {
                // Ensure array contains data and the 'type' is 'basic'
                return is_array($data) && count($data) > 0 && $data[0]['type'] === 'basic';
            }));

        $deck->saveToSession($session);
    }

    /**
     * Test saving the deck to a session with a CardGraphic.
     * Covers: saveToSession() - 'graphic' type path.
     */
    public function testSaveToSessionWithGraphicCard()
    {
        $deck = new DeckOfCards(CardStyle::Graphic); // Changed from true
        $session = $this->getMockSession();

        $session->expects($this->once())
            ->method('set')
            ->with('deck_data', $this->callback(function ($data) {
                // Ensure array contains data and the 'type' is 'graphic'
                return is_array($data) && count($data) > 0 && $data[0]['type'] === 'graphic';
            }));

        $deck->saveToSession($session);
    }


    /**
     * Test loading the deck from an empty session (should create a new deck and save it).
     * Covers: loadFromSession() - (!$deckData) path and subsequent $deck->saveToSession() call.
     */
    public function testLoadFromEmptySession()
    {
        $session = $this->getMockSession();
        $session->expects($this->once())
            ->method('get')
            ->with('deck_data')
            ->willReturn(null);

        $session->expects($this->once())
            ->method('set'); // Expect save to be called for the new deck

        $deck = DeckOfCards::loadFromSession($session);
        $this->assertEquals(52, $deck->count());
        // Newly created deck defaults to CardGraphic
        $this->assertInstanceOf('\App\Card\CardGraphic', $deck->getCards()[0]);
    }

    /**
     * Test loading the deck from a saved session with both graphic and basic cards.
     * Covers: loadFromSession() - both 'graphic' and 'basic' loading paths in the loop.
     */
    public function testLoadFromExistingSession()
    {
        // Mock data: 1 graphic card, 1 basic card
        $savedData = [
            ['suit' => 'hearts', 'value' => '2', 'type' => 'graphic'], // Covers CardGraphic load
            ['suit' => 'spades', 'value' => 'K', 'type' => 'basic'],   // Covers Card load
        ];
        $session = $this->getMockSession();
        $session->expects($this->once())
            ->method('get')
            ->with('deck_data')
            ->willReturn($savedData);
        $deck = DeckOfCards::loadFromSession($session);

        $this->assertEquals(2, $deck->count());
        $this->assertInstanceOf('\App\Card\CardGraphic', $deck->getCards()[0]);
        $this->assertInstanceOf('\App\Card\Card', $deck->getCards()[1]);
    }
}