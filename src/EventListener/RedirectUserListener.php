<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\User;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class RedirectUserListener
{
    use TargetPathTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RouterInterface $router
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->isUserLogged() && $event->isMainRequest()) {
            $currentRoute = $event->getRequest()->attributes->get('_route');

            if ($this->isAuthenticatedUserOnAnonymousPage($currentRoute)) {
                $response = new RedirectResponse($this->router->generate('homepage'));
                $event->setResponse($response);
            }
        }
    }

    private function isUserLogged(): bool
    {
        if (empty($this->tokenStorage->getToken())) {
            return false;
        }

        $user = $this->tokenStorage->getToken()->getUser();

        return $user instanceof User;
    }

    private function isAuthenticatedUserOnAnonymousPage(?string $currentRoute): bool
    {
        return in_array(
            $currentRoute,
            ['fos_user_security_login', 'fos_user_resetting_request', 'app_user_registration']
        );
    }
}
