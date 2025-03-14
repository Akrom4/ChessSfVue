# Backend Instructions for "Remember Me" Functionality

To make the "Remember Me" functionality work on the backend, update your `SecurityController.php` file's `login` method with the following changes:

```php
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
```

The key changes are:

1. Extract the `rememberMe` flag from the request data
2. Set the cookie expiration based on this flag:
   - If `rememberMe` is true: Use `withExpires()` with a DateTime object set 30 days in the future
   - If `rememberMe` is false: Don't set expiration, which creates a session cookie that expires when the browser is closed
3. Build the cookie more modularly, making the code easier to maintain

This approach makes the "Remember Me" checkbox functional by extending the cookie lifetime when the user checks the option. 