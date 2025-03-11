<?php
// src/Security/JwtCookieAuthenticator.php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class JwtCookieAuthenticator extends AbstractAuthenticator
{
    private JWTEncoderInterface $jwtEncoder;

    public function __construct(JWTEncoderInterface $jwtEncoder)
    {
        $this->jwtEncoder = $jwtEncoder;
    }

    public function supports(Request $request): bool
    {
        return $request->cookies->has('BEARER');
    }

    public function authenticate(Request $request): Passport
    {
        $jwt = $request->cookies->get('BEARER');

        try {
            // Use the JWTEncoderInterface to decode the raw JWT string
            $payload = $this->jwtEncoder->decode($jwt);
            
            if (!$payload || !isset($payload['username'])) {
                throw new AuthenticationException('Invalid JWT token.');
            }
            
            return new SelfValidatingPassport(
                new UserBadge($payload['username'])
            );
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid JWT token: ' . $e->getMessage());
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // On success, simply continue processing the request.
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Clear the cookie on failure.
        $response = new Response('Authentication failed', Response::HTTP_UNAUTHORIZED);
        $response->headers->clearCookie('BEARER');
        return $response;
    }
}
