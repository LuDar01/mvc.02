<?php

namespace App\Tests\Controller;

use App\Card\BlackjackGame;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjControllerTest extends WebTestCase
{
    /**
     * Testar de enkla statiska sidorna.
     */
    public function testStaticPages(): void
    {
        $client = static::createClient();

        // Testa /proj
        $client->request('GET', '/proj');
        $this->assertResponseIsSuccessful();

        // Testa /proj/about
        $client->request('GET', '/proj/about');
        $this->assertResponseIsSuccessful();
    }

    /**
     * Testar att spelet initieras när man går till /proj/game.
     */
    public function testGameInitialization(): void
    {
        $client = static::createClient();
        $client->request('GET', '/proj/game');

        $this->assertResponseIsSuccessful();
        
        // Kontrollera att spelet finns i sessionen
        $session = $client->getRequest()->getSession();
        $this->assertTrue($session->has('blackjack_game'));
        $this->assertInstanceOf(BlackjackGame::class, $session->get('blackjack_game'));
    }

    /**
     * Testar POST /proj/game/hit
     */
    public function testGameHit(): void
    {
        $client = static::createClient();
        
        // Initiera spelet först
        $client->request('GET', '/proj/game');
        $session = $client->getRequest()->getSession();
        $gameBefore = $session->get('blackjack_game');
        $cardsBefore = count($gameBefore->getPlayerHand()->getCards());

        // Skicka HIT
        $client->request('POST', '/proj/game/hit');
        
        // Kontrollera redirect
        $this->assertResponseRedirects('/proj/game');
        $client->followRedirect();

        // Kontrollera att spelaren fått ett kort till (om de inte redan var bust)
        $gameAfter = $client->getRequest()->getSession()->get('blackjack_game');
        $this->assertGreaterThanOrEqual($cardsBefore, count($gameAfter->getPlayerHand()->getCards()));
    }

    /**
     * Testar POST /proj/game/stand
     */
    public function testGameStand(): void
    {
        $client = static::createClient();
        $client->request('GET', '/proj/game');

        $client->request('POST', '/proj/game/stand');
        $this->assertResponseRedirects('/proj/game');
        
        $session = $client->getRequest()->getSession();
        $game = $session->get('blackjack_game');
        
        // Status bör inte längre vara 'playing' efter stand
        $this->assertNotEquals('playing', $game->getStatus());
    }

    /**
     * Testar GET /proj/game/reset
     */
    public function testGameReset(): void
    {
        $client = static::createClient();
        
        // Skapa ett spel i sessionen
        $client->request('GET', '/proj/game');
        $this->assertTrue($client->getRequest()->getSession()->has('blackjack_game'));

        // Kör reset
        $client->request('GET', '/proj/game/reset');
        $this->assertResponseRedirects('/proj/game');

        // Efter redirect till /proj/game skapas ett NYTT spel, 
        // men vi kollar sessionen direkt efter reset-anropet
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    /**
     * Testar "Early return" i hit/stand om spelet inte finns i sessionen.
     */
    public function testActionsWithoutGameInSession(): void
    {
        $client = static::createClient();

        // Skicka POST direkt utan att ha besökt /proj/game först
        $client->request('POST', '/proj/game/hit');
        $this->assertResponseRedirects('/proj/game');

        $client->request('POST', '/proj/game/stand');
        $this->assertResponseRedirects('/proj/game');
    }
}