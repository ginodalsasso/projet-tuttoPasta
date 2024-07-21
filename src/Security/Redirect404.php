<?php

namespace App\Security;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class Redirect404
{
    private $router;

    // Le constructeur prend en paramètre le service de routage pour générer des URL
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    // Méthode appelée lorsqu'une exception se produit
    public function onKernelException(ExceptionEvent $event)
    {   // Récupération de l'exception
        $exception = $event->getThrowable();
        // Si l'exception n'est pas de type NotFoundHttpException, on ne fait rien
        if ($exception instanceof NotFoundHttpException) {
            $response = new RedirectResponse($this->router->generate('app_error_404'));
            $event->setResponse($response);
        }
    }
}