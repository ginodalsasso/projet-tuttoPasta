<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect();
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(
        Request $request,
        ClientRegistry $clientRegistry,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        // ContainerInterface $container
    ) {
        try {
            $client = $clientRegistry->getClient('google');
            $googleUser = $client->fetchUser();

            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $googleUser->getEmail()]);

            if (!$existingUser) {

                $user = new User();
                $user->setEmail($googleUser->getEmail());
                $user->setVerified(true);
                $user->setPassword(bin2hex(random_bytes(16)));
                $user->setGoogleUser(true);
                $user->setRoles(['ROLE_USER']);
                $user->setUsername($googleUser->getEmail());

                $entityManager->persist($user);
                $entityManager->flush();
            } else {
                $user = $existingUser;
            }

            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            // $container->get('security.token_storage')->setToken($token);

            $event = new InteractiveLoginEvent($request, $token);
            $eventDispatcher->dispatch($event);

            // if ($container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $_SESSION['user'] = $existingUser;
            return $this->redirectToRoute('app_home');
            // } else {
            //     throw new \Exception('Échec de lauthentification après la connexion Google');
            // }

        } catch (\Exception $e) {
            return $this->redirectToRoute('app_login', ['error' => $e->getMessage()]);
        }
    }
}
