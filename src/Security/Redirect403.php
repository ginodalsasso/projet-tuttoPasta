<?php

namespace App\Security;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class Redirect403
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
    // Méthode appelée lorsqu'une exception se produit
    public function onKernelException(ExceptionEvent $event)
    {   // Récupération de l'exception
        $exception = $event->getThrowable();
        // Si l'exception n'est pas de type AccessDeniedHttpException, on ne fait rien
        if (!$exception instanceof AccessDeniedHttpException) {
            return;
        }
        // Création d'une réponse de redirection vers la page d'accueil
        if ($exception instanceof AccessDeniedHttpException) {
            $response = new RedirectResponse($this->router->generate('app_home'));
            $event->setResponse($response);
        }
    }
}