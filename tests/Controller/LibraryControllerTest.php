<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LibraryControllerTest extends WebTestCase
{
    public function testLibraryIndexRedirect(): void
    {
        $client = static::createClient();
        $client->request('GET', '/library');
        $this->assertResponseRedirects('/library/show');
    }

    public function testLibraryShowPages(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/library/show');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/library/create');
        $this->assertResponseIsSuccessful();
    }

    public function testLibraryApiRoutes(): void
    {
        $client = static::createClient();
        
        // Alla böcker API
        $client->request('GET', '/api/library/books');
        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        // Specifik bok API (ska ge 404 då ISBN inte finns)
        $client->request('POST', '/api/library/book/123456789');
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Testar att radera en bok som inte finns (för att täcka 'if (!$book)').
     */
    public function testDeleteNonExistentBook(): void
    {
        $client = static::createClient();
        $client->request('POST', '/library/delete/99999');
        $this->assertResponseRedirects('/library/show');
    }
}