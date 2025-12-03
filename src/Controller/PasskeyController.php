<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\PasskeyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

#[Route('/api/passkey')]
class PasskeyController extends AbstractController
{
    public function __construct(
        private readonly PasskeyService $passkeyService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Check if user has passkeys available
     */
    #[Route('/check', name: 'api_passkey_check', methods: ['POST'])]
    public function checkPasskeys(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $email = $data['email'] ?? null;

            if (!$email) {
                return new JsonResponse(['hasPasskeys' => false]);
            }

            $hasPasskeys = $this->passkeyService->emailHasPasskeys($email);

            return new JsonResponse(['hasPasskeys' => $hasPasskeys]);
        } catch (\Throwable $e) {
            $this->logger->error('Error checking passkeys', [
                'error' => $e->getMessage()
            ]);
            return new JsonResponse(['hasPasskeys' => false]);
        }
    }

    /**
     * Generate authentication options for login
     */
    #[Route('/authenticate/options', name: 'api_passkey_authenticate_options', methods: ['POST'])]
    public function authenticateOptions(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $email = $data['email'] ?? null;

            $options = $this->passkeyService->generateAuthenticationOptions($email);

            return new JsonResponse($this->passkeyService->serializeRequestOptions($options));
        } catch (\Throwable $e) {
            $this->logger->error('Error generating authentication options', [
                'error' => $e->getMessage()
            ]);
            return new JsonResponse([
                'error' => 'Erreur lors de la génération des options d\'authentification'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Verify authentication and log user in
     */
    #[Route('/authenticate/verify', name: 'api_passkey_authenticate_verify', methods: ['POST'])]
    public function authenticateVerify(Request $request): JsonResponse
    {
        try {
            $responseJson = $request->getContent();

            $user = $this->passkeyService->verifyAuthentication($responseJson);

            // Log the user in
            $this->loginUser($user, $request);

            return new JsonResponse([
                'success' => true,
                'redirect' => $this->generateUrl('home')
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('PassKey authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return new JsonResponse([
                'success' => false,
                'error' => 'Échec de l\'authentification PassKey',
                'details' => $e->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Generate registration options (authenticated users only)
     */
    #[Route('/register/options', name: 'api_passkey_register_options', methods: ['POST'])]
    public function registerOptions(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'error' => 'Vous devez être connecté pour enregistrer une PassKey'
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $options = $this->passkeyService->generateRegistrationOptions($user);

            return new JsonResponse($this->passkeyService->serializeCreationOptions($options));
        } catch (\Throwable $e) {
            $this->logger->error('Error generating registration options', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->getId()
            ]);
            return new JsonResponse([
                'error' => 'Erreur lors de la génération des options d\'enregistrement',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Verify registration and save credential
     */
    #[Route('/register/verify', name: 'api_passkey_register_verify', methods: ['POST'])]
    public function registerVerify(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'error' => 'Vous devez être connecté pour enregistrer une PassKey'
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = json_decode($request->getContent(), true);
            $credentialName = $data['name'] ?? 'PassKey ' . date('d/m/Y H:i');
            $credentialResponse = $data['credential'] ?? null;

            if (!$credentialResponse) {
                return new JsonResponse([
                    'error' => 'Données de credential manquantes'
                ], Response::HTTP_BAD_REQUEST);
            }

            $credential = $this->passkeyService->verifyRegistration(
                json_encode($credentialResponse),
                $user,
                $credentialName
            );

            return new JsonResponse([
                'success' => true,
                'credential' => [
                    'id' => $credential->getId(),
                    'name' => $credential->getName(),
                    'createdAt' => $credential->getCreatedAt()->format('d/m/Y H:i')
                ]
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('PassKey registration failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->getId(),
                'trace' => $e->getTraceAsString()
            ]);
            return new JsonResponse([
                'success' => false,
                'error' => 'Échec de l\'enregistrement de la PassKey: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * List user's passkeys
     */
    #[Route('/list', name: 'api_passkey_list', methods: ['GET'])]
    public function listPasskeys(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'error' => 'Non autorisé'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $passkeys = $this->passkeyService->getPasskeysForUser($user);

        return new JsonResponse([
            'passkeys' => array_map(fn($p) => [
                'id' => $p->getId(),
                'name' => $p->getName(),
                'createdAt' => $p->getCreatedAt()->format('d/m/Y H:i'),
                'lastUsedAt' => $p->getLastUsedAt()?->format('d/m/Y H:i')
            ], $passkeys)
        ]);
    }

    /**
     * Delete a passkey
     */
    #[Route('/delete/{id}', name: 'api_passkey_delete', methods: ['DELETE'])]
    public function deletePasskey(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'error' => 'Non autorisé'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $deleted = $this->passkeyService->deletePasskey($id, $user);

        if (!$deleted) {
            return new JsonResponse([
                'error' => 'PassKey non trouvée'
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * Log user in programmatically
     */
    private function loginUser(User $user, Request $request): void
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        
        $this->container->get('security.token_storage')->setToken($token);
        $request->getSession()->set('_security_main', serialize($token));

        // Dispatch the login event
        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch($event);
    }
}
