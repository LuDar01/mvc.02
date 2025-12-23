<?php

namespace App\Tests\Card;

use App\Card\Card;
use App\Card\CardGraphic;
use App\Card\DeckOfCards;
use App\Card\CardStyle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject; // Added for type hinting

/**
 * Test case for class DeckOfCards, covering all construction, draw,
 * session handling, and utility methods for 100% coverage.
 */
class DeckOfCardsTest extends TestCase
{
    /**
     * Helper to create a mock session.
     * We use PHPDoc to specify the intersection type for Scrutinizer.
     * * @return SessionInterface&MockObject
     */
    private function getMockSession(): SessionInterface
    {
        /** @var SessionInterface&MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        return $session;
    }

    // --- Constructor and Utilities Tests ---

    /**
     * Test creating a DeckOfCards object using basic cards.
     */
    public function testCreateBasicDeck()
    {
        $deck = new DeckOfCards(CardStyle::Basic);
        $this->assertInstanceOf('\App\Card\DeckOfCards', $deck);
        $this->assertEquals(52, $deck->count());
        $this->assertInstanceOf('\App\Card\Card', $deck->getCards()[0]);
        $this->assertNotInstanceOf('\App\Card\CardGraphic', $deck->getCards()[0]);
    }

    /**
     * Test creating a DeckOfCards object using graphic cards.
     */
    public function testCreateGraphicDeck()
    {
        $deck = new DeckOfCards(CardStyle::Graphic);
        $this->assertInstanceOf('\App\Card\DeckOfCards', $deck);
        $this->assertEquals(52, $deck->count());
        $this->assertInstanceOf('\App\Card\CardGraphic', $deck->getCards()[0]);
    }

    /**
     * Test the shuffle method.
     */
    public function testShuffle()
    {
        $deck = new DeckOfCards(CardStyle::Basic);
        $initialCount = $deck->count();
        $deck->shuffle();
        $this->assertEquals($initialCount, $deck->count());
    }

    // --- Draw Method Tests ---

    public function testDrawOne()
    {
        $deck = new DeckOfCards(CardStyle::Basic);
        $initialCount = $deck->count();
        $drawn = $deck->draw(1);

        $this->assertCount(1, $drawn);
        $this->assertEquals($initialCount - 1, $deck->count());
    }

    public function testDrawMultiple()
    {
        $deck = new DeckOfCards(CardStyle::Basic);
        $initialCount = $deck->count();
        $drawn = $deck->draw(5);

        $this->assertCount(5, $drawn);
        $this->assertEquals($initialCount - 5, $deck->count());
    }

    public function testDrawMoreThanAvailable()
    {
        $deck = new DeckOfCards(CardStyle::Basic);
        $drawn = $deck->draw(60);

        $this->assertCount(52, $drawn);
        $this->assertEquals(0, $deck->count());
    }

    // --- Session Handling Tests ---

    /**
     * Test saving the deck to a session.
     */
    public function testSaveToSessionBasicCard()
    {
        $deck = new DeckOfCards(CardStyle::Basic);
        /** @var SessionInterface&MockObject $session */
        $session = $this->getMockSession();

        $session->expects($this->once())
            ->method('set')
            ->with('deck_data', $this->callback(function ($data) {
                return is_array($data) && count($data) > 0 && $data[0]['type'] === 'basic';
            }));

        $deck->saveToSession($session);
    }

    public function testSaveToSessionWithGraphicCard()
    {
        $deck = new DeckOfCards(CardStyle::Graphic);
        /** @var SessionInterface&MockObject $session */
        $session = $this->getMockSession();

        $session->expects($this->once())
            ->method('set')
            ->with('deck_data', $this->callback(function ($data) {
                return is_array($data) && count($data) > 0 && $data[0]['type'] === 'graphic';
            }));

        $deck->saveToSession($session);
    }

    public function testLoadFromEmptySession()
    {
        /** @var SessionInterface&MockObject $session */
        $session = $this->getMockSession();
        $session->expects($this->once())
            ->method('get')
            ->with('deck_data')
            ->willReturn(null);

        $session->expects($this->once())
            ->method('set');

        $deck = DeckOfCards::loadFromSession($session);
        $this->assertEquals(52, $deck->count());
        $this->assertInstanceOf('\App\Card\CardGraphic', $deck->getCards()[0]);
    }

    public function testLoadFromExistingSession()
    {
        $savedData = [
            ['suit' => 'hearts', 'value' => '2', 'type' => 'graphic'],
            ['suit' => 'spades', 'value' => 'K', 'type' => 'basic'],
        ];
        /** @var SessionInterface&MockObject $session */
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