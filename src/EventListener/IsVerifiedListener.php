<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class IsVerifiedListener
{
    private Security $security;
    private RouterInterface $router;

    public function __construct(Security $security, RouterInterface $router)
    {
        $this->security = $security;
        $this->router = $router;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // 🔥 NEW: Do NOT block API routes
        if (str_starts_with($path, '/api')) {
            return;
        }

        $user = $this->security->getUser();

        // Not logged in → leave
        if (!$user) {
            return;
        }

        // Admins are allowed
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        // 👉 IMPORTANT : si lʼutilisateur est déjà vérifié → on laisse passer
        /** @var \App\Entity\User $user */

        if ($user->isVerified()) {
            return;
        }

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add('warning', 'Veuillez confirmer votre adresse email avant de continuer.');

        // Allowed routes even if NOT verified
        $allowedRoutes = [
            'app_register',
            'app_verify_email',
            'app_verify_pending',
            'app_login',
            'app_logout',
        ];

        $currentRoute = $request->attributes->get('_route');

        if (in_array($currentRoute, $allowedRoutes)) {
            return;
        }

        // Redirect
        $event->setResponse(
            new RedirectResponse($this->router->generate('app_verify_pending'))
        );
    }
}
