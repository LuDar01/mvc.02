<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CardControllerTest extends WebTestCase
{
    public function testCardHome(): void
    {
        $client = static::createClient();
        $client->request('GET', '/card');
        $this->assertResponseIsSuccessful();
    }

    public function testDeckViewWithAndWithoutSession(): void
    {
        $client = static::createClient();

        // Första anropet skapar leken (täcker "if (!$deck)")
        $client->request('GET', '/card/deck');
        $this->assertResponseIsSuccessful();

        // Andra anropet använder befintlig lek (täcker resten av loadDeck)
        $client->request('GET', '/card/deck');
        $this->assertResponseIsSuccessful();
    }

    public function testShuffleDeck(): void
    {
        $client = static::createClient();
        $client->request('GET', '/card/deck/shuffle');
        $this->assertResponseIsSuccessful();
    }

    public function testDrawOne(): void
    {
        $client = static::createClient();
        $client->request('GET', '/card/deck/draw');
        $this->assertResponseIsSuccessful();
    }

    public function testDrawMany(): void
    {
        $client = static::createClient();
        $client->request('GET', '/card/deck/draw/5');
        $this->assertResponseIsSuccessful();
    }
}
