<?php

namespace App\Security;

use App\Repository\ApiTokenRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(private readonly ApiTokenRepository $apiTokenRepo)
    {
    }

    public function supports(Request $request): ?bool
    {
        // look for header "Authorization: Bearer <token>"
        return $request->headers->has('Authorization')
            && str_starts_with($request->headers->get('Authorization'), 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $authorizationHeader = $request->headers->get('Authorization');

        // skip beyond "Bearer "
        $apiToken = substr($authorizationHeader, 7);
        if (0 === strlen($apiToken)) {
            throw new CustomUserMessageAuthenticationException('Unauthorized');
        }

        $token = $this->apiTokenRepo->findOneBy(['token' => $apiToken]);
        if (!$token) {
            throw new CustomUserMessageAuthenticationException('Unauthorized');
        }

        if ($token->isExpired()) {
            throw new CustomUserMessageAuthenticationException('Unauthorized');
        }

        return new SelfValidatingPassport(new UserBadge($token->getUser()->getUsername()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response(null, Response::HTTP_UNAUTHORIZED);
    }
}
