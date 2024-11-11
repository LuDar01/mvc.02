<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RenderTwig extends AbstractController
{
    #[Route("/", name: "me")]
    public function me(): Response
    {
        return $this->render('me.html.twig');
    }

    #[Route("/lucky", name: "lucky_number")]
    public function number(): Response
    {
        $number = random_int(0, 100);

        $data = [
            'number' => $number
        ];

        return $this->render('lucky_number.html.twig', $data);
    }

    #[Route("/about", name: "about")]
    public function about(): Response
    {
        return $this->render('about.html.twig');
    }

    #[Route("/report", name: "report")]
    public function home(): Response
    {
        return $this->render('report.html.twig');
    }

    #[Route("/api", name: "json_api")]
    public function apiLanding(): Response
    {
        // Define the list of routes
        $routes = [
            ['path' => '/api/quote',
            'name' => 'Quote of the day', 
            'description' => 'Returns a random motivational quote in JSON format.']
        ];
    
        // Render the template and pass the routes variable
        return $this->render('json_api.html.twig', [
            'routes' => $routes
        ]);
    }    

}