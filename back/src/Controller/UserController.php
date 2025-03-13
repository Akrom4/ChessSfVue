<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/api/users', name: 'api_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findAll();
        $jsonUsers = $serializer->serialize($users, 'json');

        return new JsonResponse($jsonUsers, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/users', name: 'api_user_add', methods: ['POST'])]
    public function add(Request $request, UserRepository $userRepository, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setCreatedAt(new \DateTimeImmutable());
        
        // Handle roles - with validation to ensure only admins can set ROLE_ADMIN
        if (isset($data['roles']) && is_array($data['roles'])) {
            // Optional: Check if current user has permission to set these roles
            if (in_array('ROLE_ADMIN', $data['roles']) && !$this->isGranted('ROLE_ADMIN')) {
                return new JsonResponse(['error' => 'You cannot assign ROLE_ADMIN'], JsonResponse::HTTP_FORBIDDEN);
            }
            $user->setRoles($data['roles']);
        }

        $plainPassword = $data['password'];
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $userRepository->save($user, true);

        return new JsonResponse(null, JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/users/{id}', name: 'api_user_show', methods: ['GET'])]
    public function show(User $user, SerializerInterface $serializer): JsonResponse
    {
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'user:read']);

        return new JsonResponse($jsonUser, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'api_user_edit', methods: ['PUT'])]
    public function edit(Request $request, User $user, UserRepository $userRepository, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user->setUsername($data['username'] ?? $user->getUsername());
        $user->setEmail($data['email'] ?? $user->getEmail());
        $user->setUpdatedAt(new \DateTime());

        // Handle roles - with validation
        if (isset($data['roles']) && is_array($data['roles'])) {
            // Optional: Check if current user has permission to set these roles
            if (in_array('ROLE_ADMIN', $data['roles']) && !$this->isGranted('ROLE_ADMIN')) {
                return new JsonResponse(['error' => 'You cannot assign ROLE_ADMIN'], JsonResponse::HTTP_FORBIDDEN);
            }
            $user->setRoles($data['roles']);
        }

        if (!empty($data['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        }

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $userRepository->save($user, true);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/users/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    public function delete(User $user, UserRepository $userRepository): JsonResponse
    {
        $userRepository->remove($user, true);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/me', name: 'api_user_me', methods: ['GET'])]
    public function me(SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'user:read']);
        return new JsonResponse($jsonUser, JsonResponse::HTTP_OK, [], true);
    }
}