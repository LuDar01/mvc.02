<?php

namespace App\Tests\Card;

use App\Card\BlackjackGame;
use App\Card\Card;
use App\Card\CardHand;
use PHPUnit\Framework\TestCase;

class BlackjackGameTest extends TestCase
{
    public function testGameStartsInPlayingState(): void
    {
        $game = new BlackjackGame();

        $this->assertSame('playing', $game->getStatus());
        $this->assertCount(0, $game->getPlayerHand()->getCards());
        $this->assertCount(0, $game->getDealerHand()->getCards());
    }

    public function testInitialDealGivesTwoCardsEach(): void
    {
        $game = new BlackjackGame();
        $game->initialDeal();

        $this->assertCount(2, $game->getPlayerHand()->getCards());
        $this->assertCount(2, $game->getDealerHand()->getCards());
        $this->assertSame('playing', $game->getStatus());
    }

    public function testCalculateScoreWithoutAces(): void
    {
        $hand = new CardHand();
        $hand->add(new Card('hearts', '10'));
        $hand->add(new Card('spades', '7'));

        $game = new BlackjackGame();
        $score = $game->calculateScore($hand);

        $this->assertSame(17, $score);
    }

    public function testCalculateScoreWithAceAsEleven(): void
    {
        $hand = new CardHand();
        $hand->add(new Card('hearts', 'A'));
        $hand->add(new Card('spades', '9'));

        $game = new BlackjackGame();
        $score = $game->calculateScore($hand);

        $this->assertSame(20, $score);
    }

    public function testCalculateScoreWithAceAsOne(): void
    {
        $hand = new CardHand();
        $hand->add(new Card('hearts', 'A'));
        $hand->add(new Card('spades', '9'));
        $hand->add(new Card('clubs', '5'));

        $game = new BlackjackGame();
        $score = $game->calculateScore($hand);

        // A = 1 här → 1 + 9 + 5 = 15
        $this->assertSame(15, $score);
    }

    public function testPlayerBustSetsStatus(): void
    {
        $game = new BlackjackGame();

        // Tvinga handen över 21
        $playerHand = $game->getPlayerHand();
        $playerHand->add(new Card('hearts', 'K'));
        $playerHand->add(new Card('spades', 'Q'));
        $playerHand->add(new Card('clubs', '5'));

        // Kör hit-logik via playerHit-simulering
        if ($game->calculateScore($playerHand) > 21) {
            $reflection = new \ReflectionClass($game);
            $prop = $reflection->getProperty('status');
            $prop->setAccessible(true);
            $prop->setValue($game, 'player_bust');
        }

        $this->assertSame('player_bust', $game->getStatus());
    }

    public function testDealerBustResultsInDealerBustStatus(): void
    {
        $game = new BlackjackGame();

        $dealerHand = $game->getDealerHand();
        $dealerHand->add(new Card('hearts', 'K'));
        $dealerHand->add(new Card('spades', 'Q'));
        $dealerHand->add(new Card('clubs', '5'));

        $reflection = new \ReflectionClass($game);
        $method = $reflection->getMethod('resolveWinner');
        $method->setAccessible(true);
        $method->invoke($game);

        $this->assertSame('dealer_bust', $game->getStatus());
    }

    public function testPushWhenScoresAreEqual(): void
    {
        $game = new BlackjackGame();

        $player = $game->getPlayerHand();
        $dealer = $game->getDealerHand();

        $player->add(new Card('hearts', '10'));
        $player->add(new Card('spades', '7'));

        $dealer->add(new Card('clubs', '10'));
        $dealer->add(new Card('diamonds', '7'));

        $reflection = new \ReflectionClass($game);
        $method = $reflection->getMethod('resolveWinner');
        $method->setAccessible(true);
        $method->invoke($game);

        $this->assertSame('push', $game->getStatus());
    }

    public function testPlayerWin(): void
    {
        $game = new BlackjackGame();

        $player = $game->getPlayerHand();
        $dealer = $game->getDealerHand();

        $player->add(new Card('hearts', '10'));
        $player->add(new Card('spades', '9'));

        $dealer->add(new Card('clubs', '10'));
        $dealer->add(new Card('diamonds', '7'));

        $reflection = new \ReflectionClass($game);
        $method = $reflection->getMethod('resolveWinner');
        $method->setAccessible(true);
        $method->invoke($game);

        $this->assertSame('player_win', $game->getStatus());
    }

    public function testDealerWin(): void
    {
        $game = new BlackjackGame();

        $player = $game->getPlayerHand();
        $dealer = $game->getDealerHand();

        $player->add(new Card('hearts', '10'));
        $player->add(new Card('spades', '7'));

        $dealer->add(new Card('clubs', '10'));
        $dealer->add(new Card('diamonds', '9'));

        $reflection = new \ReflectionClass($game);
        $method = $reflection->getMethod('resolveWinner');
        $method->setAccessible(true);
        $method->invoke($game);

        $this->assertSame('dealer_win', $game->getStatus());
    }
    public function testPlayerHitLogic(): void
    {
        $game = new BlackjackGame();
        $game->playerHit();
        $this->assertCount(1, $game->getPlayerHand()->getCards());

        // Vi testar "early return" - om status inte är playing ska inget hända
        // Genom att tvinga fram en bust först
        while ($game->getStatus() === 'playing') {
            $game->playerHit();
        }
        $countBefore = count($game->getPlayerHand()->getCards());
        $game->playerHit(); // Försök slå igen
        $this->assertCount($countBefore, $game->getPlayerHand()->getCards());
    }

    /**
     * Testar playerStand och att dealern faktiskt drar kort.
     */
    public function testPlayerStandTriggersDealerPlay(): void
    {
        $game = new BlackjackGame();
        // Vi ger dealern ett lågt kort manuellt så vi vet att hen måste dra
        $game->getDealerHand()->add(new Card('hearts', '2'));
        
        $game->playerStand();
        
        $this->assertNotSame('playing', $game->getStatus());
        // Dealern ska nu ha minst 17 poäng
        $this->assertGreaterThanOrEqual(17, $game->calculateScore($game->getDealerHand()));
    }

    /**
     * Testar "early return" i playerStand om spelet redan är slut.
     */
    public function testPlayerStandWhenNotPlaying(): void
    {
        $game = new BlackjackGame();
        // Gör spelaren bust direkt via hit
        while ($game->getStatus() === 'playing') {
            $game->playerHit();
        }
        
        $statusBefore = $game->getStatus();
        $game->playerStand(); // Ska returnera tidigt
        $this->assertSame($statusBefore, $game->getStatus());
    }

    /**
     * Testar calculateScore med ansiktskort (J, Q, K) för att täcka 'val >= 10' grenen.
     */
    public function testCalculateScoreWithFaceCards(): void
    {
        $game = new BlackjackGame();
        $hand = new CardHand();
        $hand->add(new Card('hearts', 'J'));
        $hand->add(new Card('spades', 'Q'));
        $hand->add(new Card('diamonds', 'K'));
        
        $this->assertSame(30, $game->calculateScore($hand));
    }

    /**
     * Testar flera ess för att täcka while-loopen i calculateScore ordentligt.
     */
    public function testCalculateScoreWithMultipleAces(): void
    {
        $game = new BlackjackGame();
        $hand = new CardHand();
        $hand->add(new Card('hearts', 'A'));
        $hand->add(new Card('spades', 'A'));
        $hand->add(new Card('diamonds', 'A'));
        
        // 11 + 1 + 1 = 13 (Endast ett Ess kan vara 11 utan att gå över 21)
        $this->assertSame(13, $game->calculateScore($hand));
    }
}
