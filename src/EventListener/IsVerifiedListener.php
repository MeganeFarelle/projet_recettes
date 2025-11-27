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

        $user = $this->security->getUser();

        // Not logged in → leave
        if (!$user) {
            return;
        }

        // Already verified → leave
        if ($user->isVerified()) {
            return;
        }

        // Allowed routes even if NOT verified
        $allowedRoutes = [
            'app_register',
            'app_verify_email',   // email confirmation link
            'app_verify_pending', // the page we will create
            'app_login',
            'app_logout',
        ];

        $currentRoute = $event->getRequest()->attributes->get('_route');

        if (in_array($currentRoute, $allowedRoutes)) {
            return;
        }

        // BLOCK ACCESS
        $event->getRequest()->getSession()->getFlashBag()->add(
            'warning',
            'Veuillez confirmer votre adresse email avant de continuer.'
        );

        // Redirect to the NEW safe page
        $event->setResponse(
            new RedirectResponse($this->router->generate('app_verify_pending'))
        );
    }
}
