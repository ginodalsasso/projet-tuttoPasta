<?php

namespace App\Controller;

use App\Domain\AntiSpam\ChallengeInterface;
use App\Domain\AntiSpam\ChanllengeGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CaptchaController extends AbstractController
{
    #[Route('/captcha', name: 'captcha')]
    public function captcha(Request $request, ChanllengeGenerator $generator): Response
    {
        return $generator->generate($request->query->get('challenge',''));
    }
}