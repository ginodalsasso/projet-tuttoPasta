<?php

namespace App\Controller;

use App\Domain\AntiSpam\ChallengeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CaptchaController extends AbstractController
{

    #[Route('/captcha', name: 'captcha')]
    // Méthode captcha qui traite la requête et génère un challenge
    public function captcha(Request $request, ChallengeGenerator $generator): Response
    {
        // Appel de la méthode generate de l'objet $generator avec le paramètre 'challenge' de la requête
        return $generator->generate($request->query->get('challenge', ''));
    }

}
