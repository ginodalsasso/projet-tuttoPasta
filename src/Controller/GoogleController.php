<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
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
    ) {
        try {
            // On récupère les informations de l'utilisateur
            $client = $clientRegistry->getClient('google');
            $googleUser = $client->fetchUser();

            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $googleUser->getEmail()]);

            if (!$existingUser) {
                $email = $googleUser->getEmail();
                $parts = explode('@', $email);
                $username = $parts[0];

                $user = new User();
                $user->setEmail($email);
                $user->setVerified(true);
                $user->setPassword(bin2hex(random_bytes(16)));
                $user->setGoogleUser(true);
                $user->setRoles(['ROLE_USER']);
                $user->setUsername($username);

                $entityManager->persist($user);
                $entityManager->flush();

            } else {
                $user = $existingUser;
            }
            
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles()); // 'main' est le nom du firewall
            // On déclenche l'événement de connexion
            $event = new InteractiveLoginEvent($request, $token);
            // On déclenche l'événement
            $eventDispatcher->dispatch($event); 
            
            $_SESSION['user'] = $existingUser;
            return $this->redirectToRoute('app_home');

        } catch (\Exception $e) {
            return $this->redirectToRoute('app_login', ['error' => $e->getMessage()]);
        }
    }
}
