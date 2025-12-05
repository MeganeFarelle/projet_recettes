<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Mime\Email;

class RegistrationController extends AbstractController
{
    // ⭐ AJOUT IMPORTANT : EmailVerifier
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        AppAuthenticator $authenticator,
        EntityManagerInterface $entityManager
    ): Response {

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setRoles(['ROLE_USER']);

            // Encode password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Save user
            $entityManager->persist($user);
            $entityManager->flush();

            // ⭐ ENVOI DE L’EMAIL DE CONFIRMATION
            $email = (new Email())
                ->from('admin@test.com')
                ->to($user->getEmail())
                ->subject('Please Confirm your Email');

            // 👉 Génère le lien + envoie l’email html
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                $email
            );

            $this->addFlash('success', 'Un email de confirmation vous a été envoyé ! Vérifiez Mailhog.');

            // Login auto après inscription
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyEmail(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $this->emailVerifier->handleEmailConfirmation($user, $request->getUri());

        /** @var \App\Entity\User $user */
        $user->setIsVerified(true);
        $em->flush();

        $this->addFlash('success', 'Votre email est vérifié !');
        return $this->redirectToRoute('app_ingredient');
    }

    #[Route('/verify/pending', name: 'app_verify_pending')]
    public function verifyPending(): Response
    {
        return $this->render('security/verify_pending.html.twig');
    }
}
