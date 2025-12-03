<?php

namespace App\Service;

use App\Entity\PasskeyCredential;
use App\Entity\User;
use App\Repository\PasskeyCredentialRepository;
use App\Repository\UserRepository;
use App\Repository\WebAuthnUserRepository;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\AuthenticatorSelectionCriteria;

class PasskeyService
{
    private const SESSION_REGISTRATION_OPTIONS = 'passkey_registration_options';
    private const SESSION_AUTHENTICATION_OPTIONS = 'passkey_authentication_options';

    public function __construct(
        private readonly PasskeyCredentialRepository $credentialRepository,
        private readonly WebAuthnUserRepository $webAuthnUserRepository,
        private readonly UserRepository $userRepository,
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $logger,
        private readonly string $rpId,
        private readonly string $rpName,
    ) {
    }

    /**
     * Generate registration options for a user
     */
    public function generateRegistrationOptions(User $user): PublicKeyCredentialCreationOptions
    {
        $rpEntity = new PublicKeyCredentialRpEntity(
            $this->rpName,
            $this->rpId
        );

        $userEntity = $this->webAuthnUserRepository->createWebAuthnUser($user);

        // Get existing credentials to exclude
        $excludeCredentials = [];
        $existingCredentials = $this->credentialRepository->findByUser($user);
        foreach ($existingCredentials as $credential) {
            $excludeCredentials[] = PublicKeyCredentialDescriptor::create(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                $credential->getPublicKeyCredentialId(),
                $credential->getTransports()
            );
        }

        $authenticatorSelection = AuthenticatorSelectionCriteria::create(
            null, // authenticatorAttachment
            AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED
        );

        $options = PublicKeyCredentialCreationOptions::create(
            $rpEntity,
            $userEntity,
            random_bytes(32),
            pubKeyCredParams: [
                PublicKeyCredentialParameters::create('public-key', \Cose\Algorithms::COSE_ALGORITHM_ES256),
                PublicKeyCredentialParameters::create('public-key', \Cose\Algorithms::COSE_ALGORITHM_RS256),
            ],
            authenticatorSelection: $authenticatorSelection,
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            excludeCredentials: $excludeCredentials,
            timeout: 60000
        );

        // Store in session
        $this->requestStack->getSession()->set(self::SESSION_REGISTRATION_OPTIONS, $options);

        return $options;
    }

    /**
     * Serialize registration options to array for JSON response
     */
    public function serializeCreationOptions(PublicKeyCredentialCreationOptions $options): array
    {
        $excludeCredentials = [];
        foreach ($options->excludeCredentials as $credential) {
            $cred = [
                'type' => $credential->type,
                'id' => Base64UrlSafe::encodeUnpadded($credential->id),
            ];
            // Ajouter transports seulement si non-vide (iOS/Safari compatibilité)
            if (!empty($credential->transports)) {
                $cred['transports'] = $credential->transports;
            }
            $excludeCredentials[] = $cred;
        }

        $result = [
            'rp' => [
                'name' => $options->rp->name,
                'id' => $options->rp->id,
            ],
            'user' => [
                'id' => Base64UrlSafe::encodeUnpadded($options->user->id),
                'name' => $options->user->name,
                'displayName' => $options->user->displayName,
            ],
            'challenge' => Base64UrlSafe::encodeUnpadded($options->challenge),
            'pubKeyCredParams' => array_map(fn($p) => [
                'type' => $p->type,
                'alg' => $p->alg,
            ], $options->pubKeyCredParams),
            'timeout' => $options->timeout ?? 60000,
            'attestation' => $options->attestation ?? 'none',
        ];

        // Ajouter excludeCredentials seulement si non-vide
        if (!empty($excludeCredentials)) {
            $result['excludeCredentials'] = $excludeCredentials;
        }

        // Ajouter authenticatorSelection sans valeurs null (iOS/Safari compatibilité)
        if ($options->authenticatorSelection) {
            $authSelection = array_filter([
                'authenticatorAttachment' => $options->authenticatorSelection->authenticatorAttachment,
                'residentKey' => $options->authenticatorSelection->residentKey,
                'userVerification' => $options->authenticatorSelection->userVerification,
            ], fn($v) => $v !== null);
            
            if (!empty($authSelection)) {
                $result['authenticatorSelection'] = $authSelection;
            }
        }

        return $result;
    }

    /**
     * Serialize request options to array for JSON response  
     */
    public function serializeRequestOptions(PublicKeyCredentialRequestOptions $options): array
    {
        $allowCredentials = [];
        foreach ($options->allowCredentials as $credential) {
            $allowCredentials[] = [
                'type' => $credential->type,
                'id' => Base64UrlSafe::encodeUnpadded($credential->id),
                'transports' => $credential->transports,
            ];
        }

        return [
            'challenge' => Base64UrlSafe::encodeUnpadded($options->challenge),
            'timeout' => $options->timeout,
            'rpId' => $options->rpId,
            'allowCredentials' => $allowCredentials,
            'userVerification' => $options->userVerification ?? 'preferred',
        ];
    }

    /**
     * Verify registration response and create credential
     */
    public function verifyRegistration(string $responseJson, User $user, string $credentialName): PasskeyCredential
    {
        $session = $this->requestStack->getSession();
        $options = $session->get(self::SESSION_REGISTRATION_OPTIONS);

        if (!$options instanceof PublicKeyCredentialCreationOptions) {
            throw new \RuntimeException('No registration options found in session');
        }

        // Create serializer
        $serializerFactory = new WebauthnSerializerFactory(
            AttestationStatementSupportManager::create()
        );
        $serializer = $serializerFactory->create();

        // Deserialize the response
        $publicKeyCredential = $serializer->deserialize($responseJson, PublicKeyCredential::class, 'json');

        if (!$publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
            throw new \RuntimeException('Invalid response type');
        }

        // Validate the response
        $ceremonyStepManagerFactory = new CeremonyStepManagerFactory();
        $ceremonyStepManager = $ceremonyStepManagerFactory->creationCeremony();

        $validator = AuthenticatorAttestationResponseValidator::create(
            ceremonyStepManager: $ceremonyStepManager
        );

        $request = $this->requestStack->getCurrentRequest();
        $host = $request?->getHost() ?? $this->rpId;

        $publicKeyCredentialSource = $validator->check(
            $publicKeyCredential->response,
            $options,
            $host
        );

        // Create and save the credential entity
        $credential = PasskeyCredential::createFromPublicKeyCredentialSource(
            $publicKeyCredentialSource,
            $user,
            $credentialName
        );

        $this->credentialRepository->save($credential, true);

        // Clean up session
        $session->remove(self::SESSION_REGISTRATION_OPTIONS);

        $this->logger->info('PassKey registered', [
            'user_id' => $user->getId(),
            'credential_name' => $credentialName
        ]);

        return $credential;
    }

    /**
     * Generate authentication options
     */
    public function generateAuthenticationOptions(?string $email = null): PublicKeyCredentialRequestOptions
    {
        $allowCredentials = [];

        // If email is provided, only allow that user's credentials
        if ($email) {
            $user = $this->userRepository->findOneBy(['email' => $email]);
            if ($user) {
                $credentials = $this->credentialRepository->findByUser($user);
                $this->logger->debug('PassKey auth options - found credentials', [
                    'email' => $email,
                    'count' => count($credentials)
                ]);
                foreach ($credentials as $credential) {
                    $credId = $credential->getPublicKeyCredentialId();
                    $this->logger->debug('PassKey auth options - credential', [
                        'id_hex' => bin2hex($credId),
                        'id_length' => strlen($credId)
                    ]);
                    $allowCredentials[] = PublicKeyCredentialDescriptor::create(
                        PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                        $credId,
                        $credential->getTransports() ?: []
                    );
                }
            }
        }

        $options = PublicKeyCredentialRequestOptions::create(
            random_bytes(32),
            $this->rpId,
            allowCredentials: $allowCredentials,
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            timeout: 60000
        );

        // Store in session
        $this->requestStack->getSession()->set(self::SESSION_AUTHENTICATION_OPTIONS, $options);

        return $options;
    }

    /**
     * Verify authentication response and return the user
     */
    public function verifyAuthentication(string $responseJson): User
    {
        $session = $this->requestStack->getSession();
        $options = $session->get(self::SESSION_AUTHENTICATION_OPTIONS);

        if (!$options instanceof PublicKeyCredentialRequestOptions) {
            throw new \RuntimeException('No authentication options found in session');
        }

        // Create serializer
        $serializerFactory = new WebauthnSerializerFactory(
            AttestationStatementSupportManager::create()
        );
        $serializer = $serializerFactory->create();

        // Deserialize the response
        $publicKeyCredential = $serializer->deserialize($responseJson, PublicKeyCredential::class, 'json');

        $this->logger->debug('PassKey authentication - credential received', [
            'rawId_hex' => bin2hex($publicKeyCredential->rawId),
            'type' => $publicKeyCredential->type
        ]);

        if (!$publicKeyCredential->response instanceof AuthenticatorAssertionResponse) {
            throw new \RuntimeException('Invalid response type');
        }

        // Find the credential source
        $credentialSource = $this->credentialRepository->findOneByCredentialId(
            $publicKeyCredential->rawId
        );

        if (!$credentialSource) {
            $this->logger->error('PassKey credential not found', [
                'rawId_hex' => bin2hex($publicKeyCredential->rawId)
            ]);
            throw new \RuntimeException('Credential not found');
        }

        // Debug: compare IDs
        $debugInfo = [
            'credential_from_db_hex' => bin2hex($credentialSource->publicKeyCredentialId),
            'credential_from_browser_hex' => bin2hex($publicKeyCredential->rawId),
            'allowCredentials_count' => count($options->allowCredentials),
            'allowCredentials_ids' => array_map(fn($c) => bin2hex($c->id), $options->allowCredentials)
        ];
        file_put_contents('/tmp/passkey_debug.log', date('Y-m-d H:i:s') . ' - ' . json_encode($debugInfo) . "\n", FILE_APPEND);
        $this->logger->debug('PassKey auth verify - comparing IDs', $debugInfo);

        // Validate the response
        $ceremonyStepManagerFactory = new CeremonyStepManagerFactory();
        $ceremonyStepManager = $ceremonyStepManagerFactory->requestCeremony();

        $validator = AuthenticatorAssertionResponseValidator::create(
            ceremonyStepManager: $ceremonyStepManager
        );

        $request = $this->requestStack->getCurrentRequest();
        $host = $request?->getHost() ?? $this->rpId;

        $publicKeyCredentialSource = $validator->check(
            $credentialSource,
            $publicKeyCredential->response,
            $options,
            $host,
            $credentialSource->userHandle
        );

        // Update the credential counter
        $this->credentialRepository->saveCredentialSource($publicKeyCredentialSource);

        // Find the user
        $user = $this->webAuthnUserRepository->findUserByHandle($publicKeyCredentialSource->userHandle);

        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        // Clean up session
        $session->remove(self::SESSION_AUTHENTICATION_OPTIONS);

        $this->logger->info('PassKey authentication successful', [
            'user_id' => $user->getId()
        ]);

        return $user;
    }

    /**
     * Check if a user has any passkeys registered
     */
    public function userHasPasskeys(User $user): bool
    {
        return $this->credentialRepository->countByUser($user) > 0;
    }

    /**
     * Check if passkeys are available for an email
     */
    public function emailHasPasskeys(string $email): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            return false;
        }
        return $this->userHasPasskeys($user);
    }

    /**
     * Get all passkeys for a user
     * 
     * @return PasskeyCredential[]
     */
    public function getPasskeysForUser(User $user): array
    {
        return $this->credentialRepository->findByUser($user);
    }

    /**
     * Delete a passkey
     */
    public function deletePasskey(int $id, User $user): bool
    {
        $credential = $this->credentialRepository->findOneByIdAndUser($id, $user);
        
        if (!$credential) {
            return false;
        }

        $this->credentialRepository->remove($credential, true);
        
        $this->logger->info('PassKey deleted', [
            'user_id' => $user->getId(),
            'credential_id' => $id
        ]);

        return true;
    }
}
