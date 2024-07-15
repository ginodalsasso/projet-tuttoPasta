<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class UserController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            // Ajoute le rôle ROLE_USER
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('admin@tuttoPasta.com', 'TuttoPasta'))
                    ->to($user->getEmail())
                    ->subject('Merci de bien confirmer votre compte afin de pouvoir vous connecter.')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email

            return  $this->redirectToRoute('app_login');
        }

        return $this->render('user/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }


    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, TranslatorInterface $translator): Response
    {

        $id = $request->query->get('id'); // retrieve the user id from the url

       // Verify the user id exists and is not null
       if (null === $id) {
           return $this->redirectToRoute('app_home');
       }

       $user = $userRepository->find($id);

       // Ensure the user exists in persistence
       if (null === $user) {
           return $this->redirectToRoute('app_home');
       }
        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Votre mail à été vérifié !');

        return $this->redirectToRoute('app_login');
    }

        // Méthode de connexion
        #[Route(path: '/login', name: 'app_login')]
        public function login(AuthenticationUtils $authenticationUtils): Response
        {
            if ($this->getUser()) {
                return $this->redirectToRoute('app_home');
            }
    
            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();
    
            // last username entered by the user
            $lastUsername = $authenticationUtils->getLastUsername();
    
            return $this->render('user/login.html.twig', [
                'last_username' => $lastUsername,
                'error' => $error,
            ]);
        }
    
        // Méthode de déconnexion
        #[Route(path: '/logout', name: 'app_logout')]
        public function logout(): void
        {
            throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
        }

        // // Méthode de vue profil
        // #[IsGranted('ROLE_USER')]
        // #[Route(path: '/profil', name: 'app_profil')]
        // public function profilShow(Security $security): Response
        // {        
        //     // Récupère l'utilisateur actuellement authentifié
        //     $user = $security->getUser();
        //     // Vérifie que l'utilisateur est bien authentifié
        //     if (!$user instanceof UserInterface) {
        //         throw new AccessDeniedException('Accès refusé');
        //     }
        
        //     return $this->render('user/profil.html.twig', [
        //         'user' => $user,
        //     ]);
        // }
        
        // // Méthode de modification des informations classiques utilisateur
        // #[IsGranted('ROLE_USER')]
        // #[Route('/user/editDara', name: 'edit_user_data')]
        // public function editUserData(Request $request, Security $security, EntityManagerInterface $entityManager): Response
        // {
        //     $user = $security->getUser();
    
        //     if (!$user) {
        //         throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        //     }
    
        //     $form = $this->createForm(UserFormType::class, $user);
    
        //     $form->handleRequest($request);
    
        //     if ($form->isSubmitted() && $form->isValid()) {
        //         $entityManager->persist($user);
        //         $entityManager->flush();
    
        //         $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
    
        //         return $this->redirectToRoute('app_profil');
        //     }
    
        //     return $this->render('user/profil.html.twig', [
        //         'form' => $form->createView(),
        //     ]);
        // }

        #[Route('/profil', name: 'app_profil')]
        #[IsGranted('ROLE_USER')]
        public function profilShow(Request $request, Security $security, EntityManagerInterface $entityManager): Response
        {
            $user = $security->getUser();

            if (!$user instanceof UserInterface) {
                throw new AccessDeniedException('Accès refusé');
            }

            $form = $this->createForm(UserFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');

                return $this->redirectToRoute('app_profil');
            }

            return $this->render('user/profil.html.twig', [
                'form' => $form->createView(),
                'user' => $user,
            ]);
        }
}
