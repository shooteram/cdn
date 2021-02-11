<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    public function supports(Request $request)
    {
        return $request->headers->has('x-auth-token');
    }

    public function getCredentials(Request $request)
    {
        return $request->headers->get('x-auth-token');
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (null === $credentials) {
            return null;
        }

        return $userProvider->loadUserByUsername($credentials);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->response($request);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return $this->response($request);
    }

    public function supportsRememberMe()
    {
        return false;
    }

    private function response(Request $request): Response
    {
        return 'application/json' === $request->headers->get('accept')
            ? new JsonResponse(['response' => 'unauthorized.'], Response::HTTP_UNAUTHORIZED)
            : new Response('unauthorized.', Response::HTTP_UNAUTHORIZED);
    }
}
