<?php
// src/Controller/WelcomeController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeController extends AbstractController
{
    #[Route('/welcome', name: 'app_welcome')]
    public function welcome(): Response
    {
        return $this->render('welcome.html.twig');
    }
}
