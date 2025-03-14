<?php
// src/Controller/SecurityController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SecurityController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserProviderInterface $userProvider,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        // Get the rememberMe value from the request
        $rememberMe = $data['rememberMe'] ?? false;

        try {
            $user = $userProvider->loadUserByIdentifier($username);

            if (!$passwordHasher->isPasswordValid($user, $password)) {
                throw new AuthenticationException('Invalid credentials.');
            }

            $token = $jwtManager->create($user);

            $response = new JsonResponse(['message' => 'Login successful']);
            
            // Create base cookie
            $cookieBuilder = Cookie::create('BEARER', $token)
                ->withHttpOnly(true)
                ->withSameSite(Cookie::SAMESITE_LAX)
                ->withPath('/');
            
            // Set expiration based on rememberMe flag
            if ($rememberMe) {
                // Set expiration to 30 days in the future if rememberMe is true
                $expirationDate = new \DateTime('+30 days');
                $cookieBuilder = $cookieBuilder->withExpires($expirationDate);
            }
            
            // Add secure and domain settings for production
            if ($_ENV['APP_ENV'] === 'prod') {
                $cookieBuilder = $cookieBuilder
                    ->withSecure(true)
                    ->withDomain('.akrom.xyz');
            }
            
            $response->headers->setCookie($cookieBuilder);

            return $response;
        } catch (AuthenticationException $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_UNAUTHORIZED);
        }
    }

    
    #[Route(path: '/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        $response = new JsonResponse(['message' => 'Logged out successfully.']);
        
        // Clear the authentication cookie
        $response->headers->clearCookie('BEARER', '/', null, false, true);
        
        return $response;
    }
}
