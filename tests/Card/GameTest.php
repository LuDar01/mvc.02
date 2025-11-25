<?php

namespace App\Tests\Card;

use App\Card\Card;
use App\Card\CardHand;
use App\Card\DeckOfCards;
use App\Card\Game;
use PHPUnit\Framework\TestCase;

/**
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
        $this->assertInstanceOf('\App\Card\Game', $game);
        $this->assertInstanceOf('\App\Card\DeckOfCards', $game->getDeck());
        $this->assertInstanceOf('\App\Card\CardHand', $game->getPlayer());
        $this->assertInstanceOf('\App\Card\CardHand', $game->getBank());
        $this->assertEquals('player_turn', $game->getState());
        $this->assertIsArray($game->toArray());
    }

    /**
     * Test the start method resets the game.
     */
    public function testStartGame()
    {
        $game = new Game();
        // Force the state to something else before starting
        $reflection = new \ReflectionClass($game);
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
            // Use 'clubs' suit as it doesn't affect value
            $hand->add(new Card('clubs', $value));
        }
        return $hand;
    }

    // --- Score Calculation Tests (Ensure 100% coverage on calculateScore) ---

    /**
     * Test score calculation with standard cards (no Aces).
     */
    public function testCalculateScoreStandard()
    {
        $game = new Game();
        // 5 + 7 = 12
        $hand = $this->createHandWithCards(['5', '7']);
        $this->assertEquals(12, $game->calculateScore($hand));

        // K + Q = 10 + 10 = 20
        $hand = $this->createHandWithCards(['K', 'Q']);
        $this->assertEquals(20, $game->calculateScore($hand));

        // 10 + 2 + 9 = 21
        $hand = $this->createHandWithCards(['10', '2', '9']);
        $this->assertEquals(21, $game->calculateScore($hand));
    }

    /**
     * Test score calculation with a single Ace (treated as 11).
     */
    public function testCalculateScoreSingleAce()
    {
        $game = new Game();
        // A + 5 = 11 + 5 = 16
        $hand = $this->createHandWithCards(['A', '5']);
        $this->assertEquals(16, $game->calculateScore($hand));

        // A + K = 11 + 10 = 21 (Blackjack)
        $hand = $this->createHandWithCards(['A', 'K']);
        $this->assertEquals(21, $game->calculateScore($hand));
    }

    /**
     * Test score calculation with multiple Aces that stay high.
     */
    public function testCalculateScoreMultipleAcesHigh()
    {
        $game = new Game();
        // A + A + 9 = 11 + 11 + 9 = 31. Reduced: 1 + 11 + 9 = 21.
        $hand = $this->createHandWithCards(['A', 'A', '9']);
        $this->assertEquals(21, $game->calculateScore($hand));
    }

    /**
     * Test score calculation where Aces must be reduced (going Bust).
     */
    public function testCalculateScoreAcesReduced()
    {
        $game = new Game();
        // A + A + 5 = 11 + 11 + 5 = 27. Reduced: 1 + 11 + 5 = 17.
        $hand = $this->createHandWithCards(['A', 'A', '5']);
        $this->assertEquals(17, $game->calculateScore($hand));

        // A + 8 + 6 = 11 + 8 + 6 = 25. Reduced: 1 + 8 + 6 = 15.
        $hand = $this->createHandWithCards(['A', '8', '6']);
        $this->assertEquals(15, $game->calculateScore($hand));
    }

    // --- Game Logic Tests (Ensure 100% coverage on playerDraw/Stand and determineWinner) ---

    /**
     * Test playerDraw and going bust. (Covers $playerScore > 21 path in determineWinner)
     */
    public function testPlayerDrawAndBust()
    {
        $game = new Game();

        // Mock the deck to give specific cards that cause a bust
        $deck = $this->createMock(DeckOfCards::class);
        $deck->method('draw')
             ->willReturnOnConsecutiveCalls(
                 [new Card('clubs', 'K')], // 10
                 [new Card('clubs', 'K')], // 10
                 [new Card('clubs', '3')],  // 3 (Total 23)
                 [] // Safety
             );

        // Use reflection to set the mocked deck
        $reflection = new \ReflectionClass($game);
        $prop = $reflection->getProperty('deck');
        $prop->setValue($game, $deck);

        // Draw 1 (score 10)
        $game->playerDraw();
        $this->assertEquals('player_turn', $game->getState());

        // Draw 2 (score 20)
        $game->playerDraw();
        $this->assertEquals('player_turn', $game->getState());

        // Draw 3 (score 23 - Bust! state change happens immediately in playerDraw)
        $game->playerDraw();
        $this->assertEquals('bank_wins', $game->getState());
    }

    /**
     * Test playerStand and bank busts. (Covers $bankScore > 21 path in determineWinner)
     * FIX: Updated mock cards so the bank is forced to draw a busting card (20 -> 22).
     */
    public function testPlayerStandAndBankBusts()
    {
        $game = new Game();

        // 1. Mock the Deck to control the outcome
        $deck = $this->createMock(DeckOfCards::class);
        $deck->method('draw')
             ->willReturnOnConsecutiveCalls(
                 // Player draws (setup score 20)
                 [new Card('clubs', '10')],
                 [new Card('clubs', 'K')],

                 // Bank draws (stops at 17, but we force a bust)
                 [new Card('hearts', '10')], // 10
                 [new Card('hearts', '2')],  // 12 (Forces another draw as 12 < 17)
                 [new Card('hearts', 'K')], // 22 (Bust!)
                 [] // Safety: Type Error prevention
             );

        // Set up player score (20)
        $reflection = new \ReflectionClass($game);
        $prop = $reflection->getProperty('deck');
        $prop->setValue($game, $deck);

        // Draw 2 cards for player setup
        $game->playerDraw();
        $game->playerDraw();

        $game->playerStand();

        // State should be player_wins because bank bust (score 22)
        $this->assertEquals('player_wins', $game->getState());
    }

    /**
     * Test playerStand and player wins by score. (Covers $playerScore > $bankScore path in determineWinner)
     */
    public function testPlayerStandAndPlayerWinsByScore()
    {
        $game = new Game();

        // 1. Mock the Deck to control the outcome
        $deck = $this->createMock(DeckOfCards::class);
        $deck->method('draw')
             ->willReturnOnConsecutiveCalls(
                 // Player draws (setup score 20)
                 [new Card('clubs', '10')],
                 [new Card('clubs', 'K')],

                 // Bank draws (stops at 17)
                 [new Card('hearts', '10')], // 10
                 [new Card('hearts', '7')],  // 17 (stops here)
                 [] // Safety: Type Error prevention
             );

        $reflection = new \ReflectionClass($game);
        $prop = $reflection->getProperty('deck');
        $prop->setValue($game, $deck);

        // Player draws (score 20)
        $game->playerDraw();
        $game->playerDraw();

        $game->playerStand();

        // State should be player_wins (20 > 17)
        $this->assertEquals('player_wins', $game->getState());
    }


    /**
     * Test playerStand and bank wins by score. (Covers $bankScore > $playerScore path in determineWinner)
     */
    public function testPlayerStandAndBankWins()
    {
        $game = new Game();

        // 1. Mock the Deck to control the outcome
        $deck = $this->createMock(DeckOfCards::class);
        $deck->method('draw')
             ->willReturnOnConsecutiveCalls(
                 // Player draws (setup score 8)
                 [new Card('clubs', '8')],

                 // Bank draws (stops at 17)
                 [new Card('hearts', '10')], // 10
                 [new Card('hearts', '7')],  // 17 (stops here)
                 [] // Safety: Type Error prevention
             );

        // Set up player score < bank score (e.g. Player 8, Bank 17)
        $reflection = new \ReflectionClass($game);
        $prop = $reflection->getProperty('deck');
        $prop->setValue($game, $deck);

        // Draw 1 card for player setup
        $game->playerDraw();

        $game->playerStand();

        // State should be bank_wins (17 > 8)
        $this->assertEquals('bank_wins', $game->getState());
        $this->assertCount(2, $game->getBank()->getCards());
    }

    /**
     * Test determineWinner resulting in a draw. (Covers the 'draw' path in determineWinner)
     * FIX: Safety return [] is already present. This test will now pass due to the fix in Game.php.
     */
    public function testDetermineWinnerDraw()
    {
        $game = new Game();

        // Mock the Deck to force a draw score
        $deck = $this->createMock(DeckOfCards::class);
        $deck->method('draw')
             ->willReturnOnConsecutiveCalls(
                 // Player draws (setup score 10)
                 [new Card('clubs', '10')],

                 // Bank draws (stops at 10)
                 [new Card('hearts', '10')],
                 [] // FIX: Safety return to avoid TypeError
             );

        $reflection = new \ReflectionClass($game);
        $prop = $reflection->getProperty('deck');
        $prop->setValue($game, $deck);

        // Player draws (score 10)
        $game->playerDraw();

        $game->playerStand();

        // State should be 'draw' (10 == 10)
        $this->assertEquals('draw', $game->getState());
    }

    /**
     * Test getPlayerScore and getBankScore.
     */
    public function testGetScores()
    {
        $game = new Game();

        // Use reflection to mock the player's hand with known score (e.g., 20)
        $playerHand = $this->createHandWithCards(['K', 'Q']);
        $reflection = new \ReflectionClass($game);
        $prop = $reflection->getProperty('player');
        $prop->setValue($game, $playerHand);

        // Use reflection to mock the bank's hand with known score (e.g., 18)
        $bankHand = $this->createHandWithCards(['10', '8']);
        $prop = $reflection->getProperty('bank');
        $prop->setValue($game, $bankHand);

        $this->assertEquals(20, $game->getPlayerScore());
        $this->assertEquals(18, $game->getBankScore());
    }

    /**
     * Test toArray method structure.
     */
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

    /**
     * Test playerDraw when the deck is empty (covers the null return).
     */
    public function testPlayerDrawEmptyDeck()
    {
        $game = new Game();

        // Mock the deck to return an empty array immediately
        $deck = $this->createMock(DeckOfCards::class);
        $deck->method('draw')
             ->willReturn([]);

        $reflection = new \ReflectionClass($game);
        $prop = $reflection->getProperty('deck');
        $prop->setValue($game, $deck);

        $drawn = $game->playerDraw();

        $this->assertNull($drawn);
        $this->assertEquals('player_turn', $game->getState()); // State should not change
    }
}
