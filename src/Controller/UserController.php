<?php

namespace App\Controller;

use App\Domain\AntiSpam\ChallengeInterface;
use App\Entity\User;
use App\Form\UserFormType;
use App\Form\EditPasswordType;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AppointmentRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class UserController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

//_____________________________________________________________REGISTER/LOGIN/LOGOUT_____________________________________________________________
//____________________________________________________________________________________________________________________________
//____________________________________________________________________________________________________________________
// ---------------------------------Méthode d'inscription--------------------------------- //
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, ChallengeInterface $challenge): Response
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
                    ->htmlTemplate('emails/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email

            return  $this->redirectToRoute('app_login');
            $this->addFlash('success', 'Un email de confirmation vous a été envoyé, pour confirmer votre compte');
        }

        return $this->render('user/register.html.twig', [
            'registrationForm' => $form,
            'challenge' => $challenge->generateKey()
        ]);
    }

// ---------------------------------Méthode de vérification d'email--------------------------------- //
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


// ---------------------------------Méthode de connexion--------------------------------- //
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Intercepte l'erreur d'authentification s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier username entré par l'utilisateur
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
        throw new \LogicException('Cette méthode peut être vide - elle sera interceptée par la clé de déconnexion de votre pare-feu.');
    }

//________________________________________________________________AFFICHAGE________________________________________________________________
//____________________________________________________________________________________________________________________________
//____________________________________________________________________________________________________________________
// ---------------------------------Affichage profil utilisateur--------------------------------- //
    #[Route('/profil', name: 'app_profil', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function profil(Security $security, AppointmentRepository $appointmentRepository): Response
    {
        $user = $security->getUser();

        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        // Formulaire pour les informations utilisateur
        $form = $this->createForm(UserFormType::class, $user);

        // Formulaire pour le changement de mot de passe
        $passwordForm = $this->createForm(EditPasswordType::class, $user);

        $appointments = $appointmentRepository->findByUser($user);

        return $this->render('user/profil.html.twig', [
            'form' => $form->createView(),
            'passwordForm' => $passwordForm->createView(),
            'user' => $user,
            'appointments' => $appointments,
        ]);
    }

//________________________________________________________________CRUD________________________________________________________________
//____________________________________________________________________________________________________________________________
//____________________________________________________________________________________________________________________
// ---------------------------------Edition infos utilisateur--------------------------------- //
    #[Route('/profil/update-info', name: 'app_profil_update_info', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateInfo(Request $request, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = $security->getUser();

        // Formulaire pour les informations utilisateur
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        // Gestion du formulaire des informations utilisateur
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
        }

        return $this->redirectToRoute('app_profil');
    }
    
// ---------------------------------Edition password utilisateur--------------------------------- //
    #[Route('/profil/update-password', name: 'app_profil_update_password', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updatePassword(Request $request, Security $security, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $security->getUser();

        // Formulaire pour le changement de mot de passe
        $passwordForm = $this->createForm(EditPasswordType::class, $user);
        $passwordForm->handleRequest($request);

        // Gestion du formulaire de changement de mot de passe
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $oldPassword = $passwordForm->get('oldPassword')->getData();

            // Vérifiez que l'ancien mot de passe est correct
            if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
                $this->addFlash('error', 'L\'ancien mot de passe est incorrect.');
            } else {
                // Hashage et mise à jour du mot de passe
                $newPassword = $passwordForm->get('plainPassword')->getData();
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été changé avec succès.');
            }
        }

        return $this->redirectToRoute('app_profil');
    }
    

// --------------------------------- Suppression d'un compte utilisateur--------------------------------- //
    #[Route('/delete_account', name: 'app_delete_account')]
    #[IsGranted('ROLE_USER')]
    public function deleteAccount(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, CommentRepository $commentRepository
    ): RedirectResponse
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        // Récupérer et anonymiser les commentaires de l'utilisateur
        $comments = $commentRepository->findBy(['user' => $user]);
        foreach ($comments as $comment) {
            $comment->setUser(null);
            $comment->setUsername('Utilisateur supprimé');
            $entityManager->persist($comment);
        }

        // Supprime l'utilisateur de la base de données
        $entityManager->remove($user);
        $entityManager->flush();

        // Déconnecte l'utilisateur après la suppression du compte
        $tokenStorage->setToken(null);

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');

        // Redirige vers la page d'accueil après la suppression du compte
        return $this->redirectToRoute('app_home');
    }
        
}
