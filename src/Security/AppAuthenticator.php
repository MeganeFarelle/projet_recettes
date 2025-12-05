<?php

namespace App\Security;

use App\Service\SendMailService;
use App\Security\EmailVerifier;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private SendMailService $mailService;
    private EmailVerifier $emailVerifier;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        SendMailService $mailService,
        EmailVerifier $emailVerifier
    ) {
        $this->mailService = $mailService;
        $this->emailVerifier = $emailVerifier;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var \App\Entity\User $user */
        $user = $token->getUser();

        // ⭐ ADMIN = PAS BESOIN DE VÉRIFIER EMAIL
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            
            // ⭐ USER normal doit vérifier son email
            if (!$user->isVerified()) {

                // Renvoi du mail de vérification
                $email = (new Email())
                    ->from('admin@test.com')
                    ->to($user->getEmail())
                    ->subject('Veuillez confirmer votre email');

                $this->emailVerifier->sendEmailConfirmation(
                    'app_verify_email',
                    $user,
                    $email
                );

                throw new CustomUserMessageAuthenticationException(
                    'Votre email n’est pas vérifié. Un nouveau lien de confirmation a été envoyé.'
                );
            }
        }

        // ⭐ Envoi du mail de bienvenue
        $this->mailService->dire_bonjour($user);

        // ⭐ Redirection si URL mémorisée
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Sinon vers les ingrédients
        return new RedirectResponse($this->urlGenerator->generate('app_ingredient'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
