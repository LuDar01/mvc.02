<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class JsonQuote
{
    // JSON route for a random quote
    #[Route("/api/quote", name: "api_quote")]
    public function jsonQuote(): Response
    {
        $quotes = [
            'New day, new challenges.',
            'Love yourself.',
            'Be yourself, everybody else is taken!'
        ];
        $rand = array_rand($quotes);
        $randomQuote = $quotes[$rand];

        $data = [
            'lucky-quote' => $randomQuote,
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

}
