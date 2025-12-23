<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GameControllerTest extends WebTestCase
{
    public function testGameHomeAndDoc(): void
    {
        $client = static::createClient();
        $client->request('GET', '/game');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/game/doc');
        $this->assertResponseIsSuccessful();
    }

    /**
     * Testar omdirigeringar när sessionen är tom.
     */
    public function testRedirectsWithoutSession(): void
    {
        $client = static::createClient();

        $routes = ['/game/play', '/game/draw', '/game/stand'];
        foreach ($routes as $route) {
            $client->request('GET', $route);
            $this->assertResponseRedirects('/game/start');
        }
    }

    /**
     * Testar hela flödet med aktiv session för 100% täckning.
     */
    public function testFullGameFlow(): void
    {
        $client = static::createClient();

        // 1. Skapa spelet (Anropar start())
        $client->request('GET', '/game/start');
        $this->assertResponseRedirects('/game/play');
        $client->followRedirect();

        // 2. Nu finns 'game' i sessionen, testa play() fullt ut
        $this->assertResponseIsSuccessful();

        // 3. Testa draw() med aktivt spel
        $client->request('GET', '/game/draw');
        $this->assertResponseRedirects('/game/play');

        // 4. Testa stand() med aktivt spel
        $client->request('GET', '/game/stand');
        $this->assertResponseRedirects('/game/play');

        // 5. Testa API med aktivt spel
        $client->request('GET', '/api/game');
        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        // 6. Testa reset()
        $client->request('GET', '/game/reset');
        $this->assertResponseRedirects('/game/start');
    }
}
