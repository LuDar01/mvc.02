<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjController extends AbstractController
{
    #[Route("/proj", name: "proj_home", methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('proj/index.html.twig');
    }

    #[Route("/proj/about", name: "proj_about", methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('proj/about.html.twig');
    }

    #[Route("/proj/game", name: "proj_game", methods: ['GET'])]
    public function game(): Response
    {
        // Denna vänds till spelet senare
        return $this->render('proj/game.html.twig');
    }
}   