<?php

namespace App\Tests\Card;

use App\Card\Card;
use App\Card\CardHand;
use App\Card\DeckOfCards;
use App\Card\Game;
use App\Card\CardStyle;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 *
 * Test case for class Game, covering 100% of the game logic.
 */
class GameTest extends TestCase
{
    /**
     * Test creating a Game object and its initial state.
     */
    public function testCreateGameObject()
    {
        $game = new Game();
        $this->assertInstanceOf(Game::class, $game);
        $this->assertInstanceOf(DeckOfCards::class, $game->getDeck());
        $this->assertInstanceOf(CardHand::class, $game->getPlayer());
        $this->assertInstanceOf(CardHand::class, $game->getBank());
        $this->assertEquals('player_turn', $game->getState());
        $this->assertIsArray($game->toArray());
    }

    /**
     * Test the start method resets the game.
     */
    public function testStartGame()
    {
        $game = new Game();

        $reflection = new ReflectionClass($game);
        $prop = $reflection->getProperty('state');
        $prop->setValue($game, 'finished');

        $game->start();

        $this->assertEquals('player_turn', $game->getState());
        $this->assertEmpty($game->getPlayer()->getCards());
        $this->assertEmpty($game->getBank()->getCards());
    }

    /**
     * Helper to mock a card hand with specific cards for score calculation.
     */
    private function createHandWithCards(array $values): CardHand
    {
        $hand = new CardHand();
        foreach ($values as $value) {
            $hand->add(new Card('clubs', $value));
        }
        return $hand;
    }

    public function testCalculateScoreStandard()
    {
        $game = new Game();

        $hand = $this->createHandWithCards(['5', '7']);
        $this->assertEquals(12, $game->calculateScore($hand));

        $hand = $this->createHandWithCards(['K', 'Q']);
        $this->assertEquals(20, $game->calculateScore($hand));

        $hand = $this->createHandWithCards(['10', '2', '9']);
        $this->assertEquals(21, $game->calculateScore($hand));
    }

    public function testCalculateScoreSingleAce()
    {
        $game = new Game();

        $hand = $this->createHandWithCards(['A', '5']);
        $this->assertEquals(16, $game->calculateScore($hand));

        $hand = $this->createHandWithCards(['A', 'K']);
        $this->assertEquals(21, $game->calculateScore($hand));
    }

    public function testCalculateScoreMultipleAcesHigh()
    {
        $game = new Game();

        $hand = $this->createHandWithCards(['A', 'A', '9']);
        $this->assertEquals(21, $game->calculateScore($hand));
    }

    public function testCalculateScoreAcesReduced()
    {
        $game = new Game();

        $hand = $this->createHandWithCards(['A', 'A', '5']);
        $this->assertEquals(17, $game->calculateScore($hand));

        $hand = $this->createHandWithCards(['A', '8', '6']);
        $this->assertEquals(15, $game->calculateScore($hand));
    }

    public function testPlayerDrawAndBust()
    {
        $game = new Game();

        $deck = $this->createMock(DeckOfCards::class);
        $deck->method('draw')->willReturnOnConsecutiveCalls(
            [new Card('clubs', 'K')],
            [new Card('clubs', 'K')],
            [new Card('clubs', '3')],
            []
        );

        $reflection = new ReflectionClass($game);
        $prop = $reflection->getProperty('deck');
        $prop->setValue($game, $deck);

        $game->playerDraw();
        $game->playerDraw();
        $game->playerDraw();

        $this->assertEquals('bank_wins', $game->getState());
    }

    public function testPlayerStandAndBankBusts()
    {
        $game = new Game();

        $deck = $this->createMock(DeckOfCards::class);
        $deck->method('draw')->willReturnOnConsecutiveCalls(
            [new Card('clubs', '10')],
            [new Card('clubs', 'K')],
            [new Card('hearts', '10')],
            [new Card('hearts', '2')],
            [new Card('hearts', 'K')],
            []
        );

        $reflection = new ReflectionClass($game);
        $prop = $reflection->getProperty('deck');
        $prop->setValue($game, $deck);

        $game->playerDraw();
        $game->playerDraw();
        $game->playerStand();

        $this->assertEquals('player_wins', $game->getState());
    }

    public function testPlayerStandAndPlayerWinsByScore()
    {
        $game = new Game();

        $deck = $this->createMock(DeckOfCards::class);
        $deck->method('draw')->willReturnOnConsecutiveCalls(
            [new Card('clubs', '10')],
            [new Card('clubs', 'K')],
            [new Card('hearts', '10')],
            [new Card('hearts', '7')],
            []
        );

        $reflection = new ReflectionClass($game);
        $prop = $reflection->getProperty('deck');
        $prop->setValue($game, $deck);

        $game->playerDraw();
        $game->playerDraw();
        $game->playerStand();

        $this->assertEquals('player_wins', $game->getState());
    }

    public function testPlayerStandAndBankWins()
    {
        $game = new Game();

        $deck = $this->createMock(DeckOfCards::class);
        $deck->method('draw')->willReturnOnConsecutiveCalls(
            [new Card('clubs', '8')],
            [new Card('hearts', '10')],
            [new Card('hearts', '7')],
            []
        );

        $reflection = new ReflectionClass($game);
        $prop = $reflection->getProperty('deck');
        $prop->setValue($game, $deck);

        $game->playerDraw();
        $game->playerStand();

        $this->assertEquals('bank_wins', $game->getState());
        $this->assertCount(2, $game->getBank()->getCards());
    }

    public function testDetermineWinnerDraw()
    {
        $game = new Game();

        $deck = $this->createMock(DeckOfCards::class);
        $deck->method('draw')->willReturnOnConsecutiveCalls(
            [new Card('clubs', '10')],
            [new Card('hearts', '10')],
            []
        );

        $reflection = new ReflectionClass($game);
        $prop = $reflection->getProperty('deck');
        $prop->setValue($game, $deck);

        $game->playerDraw();
        $game->playerStand();

        $this->assertEquals('draw', $game->getState());
    }

    public function testGetScores()
    {
        $game = new Game();

        $reflection = new ReflectionClass($game);

        $playerHand = $this->createHandWithCards(['K', 'Q']);
        $prop = $reflection->getProperty('player');
        $prop->setValue($game, $playerHand);

        $bankHand = $this->createHandWithCards(['10', '8']);
        $prop = $reflection->getProperty('bank');
        $prop->setValue($game, $bankHand);

        $this->assertEquals(20, $game->getPlayerScore());
        $this->assertEquals(18, $game->getBankScore());
    }

    public function testToArrayStructure()
    {
        $game = new Game();
        $array = $game->toArray();

        $this->assertArrayHasKey('player_cards', $array);
        $this->assertArrayHasKey('player_score', $array);
        $this->assertArrayHasKey('bank_cards', $array);
        $this->assertArrayHasKey('bank_score', $array);
        $this->assertArrayHasKey('game_state', $array);
        $this->assertArrayHasKey('deck_count', $array);
    }

    public function testPlayerDrawEmptyDeck()
    {
        $game = new Game();

        $deck = $this->createMock(DeckOfCards::class);
        $deck->method('draw')->willReturn([]);

        $reflection = new ReflectionClass($game);
        $prop = $reflection->getProperty('deck');
        $prop->setValue($game, $deck);

        $drawn = $game->playerDraw();

        $this->assertNull($drawn);
        $this->assertEquals('player_turn', $game->getState());
    }
}
