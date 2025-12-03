<?php

namespace App\Entity;

use App\Repository\PasskeyCredentialRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\TrustPath;

#[ORM\Entity(repositoryClass: PasskeyCredentialRepository::class)]
#[ORM\Table(name: 'passkey_credential')]
#[ORM\Index(columns: ['user_id'], name: 'idx_passkey_user')]
class PasskeyCredential
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::BINARY, length: 255)]
    private string $publicKeyCredentialId;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $type;

    #[ORM\Column(type: Types::JSON)]
    private array $transports = [];

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $attestationType;

    #[ORM\Column(type: Types::JSON)]
    private array $trustPath = [];

    #[ORM\Column(type: Types::BINARY)]
    private string $aaguid;

    #[ORM\Column(type: Types::BINARY)]
    private string $credentialPublicKey;

    #[ORM\Column(type: Types::BINARY)]
    private string $userHandle;

    #[ORM\Column(type: Types::INTEGER)]
    private int $counter;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $otherUI = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $backupEligible = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $backupStatus = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $uvInitialized = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastUsedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicKeyCredentialId(): string
    {
        return is_resource($this->publicKeyCredentialId) 
            ? stream_get_contents($this->publicKeyCredentialId) 
            : $this->publicKeyCredentialId;
    }

    public function setPublicKeyCredentialId(string $publicKeyCredentialId): static
    {
        $this->publicKeyCredentialId = $publicKeyCredentialId;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getTransports(): array
    {
        return $this->transports;
    }

    public function setTransports(array $transports): static
    {
        $this->transports = $transports;
        return $this;
    }

    public function getAttestationType(): string
    {
        return $this->attestationType;
    }

    public function setAttestationType(string $attestationType): static
    {
        $this->attestationType = $attestationType;
        return $this;
    }

    public function getTrustPath(): array
    {
        return $this->trustPath;
    }

    public function setTrustPath(array $trustPath): static
    {
        $this->trustPath = $trustPath;
        return $this;
    }

    public function getAaguid(): string
    {
        return is_resource($this->aaguid) 
            ? stream_get_contents($this->aaguid) 
            : $this->aaguid;
    }

    public function setAaguid(string $aaguid): static
    {
        $this->aaguid = $aaguid;
        return $this;
    }

    public function getCredentialPublicKey(): string
    {
        return is_resource($this->credentialPublicKey) 
            ? stream_get_contents($this->credentialPublicKey) 
            : $this->credentialPublicKey;
    }

    public function setCredentialPublicKey(string $credentialPublicKey): static
    {
        $this->credentialPublicKey = $credentialPublicKey;
        return $this;
    }

    public function getUserHandle(): string
    {
        return is_resource($this->userHandle) 
            ? stream_get_contents($this->userHandle) 
            : $this->userHandle;
    }

    public function setUserHandle(string $userHandle): static
    {
        $this->userHandle = $userHandle;
        return $this;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function setCounter(int $counter): static
    {
        $this->counter = $counter;
        return $this;
    }

    public function getOtherUI(): ?array
    {
        return $this->otherUI;
    }

    public function setOtherUI(?array $otherUI): static
    {
        $this->otherUI = $otherUI;
        return $this;
    }

    public function isBackupEligible(): ?bool
    {
        return $this->backupEligible;
    }

    public function setBackupEligible(?bool $backupEligible): static
    {
        $this->backupEligible = $backupEligible;
        return $this;
    }

    public function getBackupStatus(): ?bool
    {
        return $this->backupStatus;
    }

    public function setBackupStatus(?bool $backupStatus): static
    {
        $this->backupStatus = $backupStatus;
        return $this;
    }

    public function getUvInitialized(): ?string
    {
        return $this->uvInitialized;
    }

    public function setUvInitialized(?string $uvInitialized): static
    {
        $this->uvInitialized = $uvInitialized;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastUsedAt(): ?\DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(?\DateTimeImmutable $lastUsedAt): static
    {
        $this->lastUsedAt = $lastUsedAt;
        return $this;
    }

    /**
     * Convert to PublicKeyCredentialSource
     */
    public function toPublicKeyCredentialSource(): PublicKeyCredentialSource
    {
        return PublicKeyCredentialSource::create(
            $this->getPublicKeyCredentialId(),
            $this->type,
            $this->transports,
            $this->attestationType,
            TrustPath\EmptyTrustPath::create(),
            \Symfony\Component\Uid\Uuid::fromBinary($this->getAaguid()),
            $this->getCredentialPublicKey(),
            $this->getUserHandle(),
            $this->counter,
            $this->otherUI,
            $this->backupEligible,
            $this->backupStatus,
            $this->uvInitialized
        );
    }

    /**
     * Create from PublicKeyCredentialSource
     */
    public static function createFromPublicKeyCredentialSource(
        PublicKeyCredentialSource $source,
        User $user,
        string $name
    ): self {
        $credential = new self();
        $credential->setPublicKeyCredentialId($source->publicKeyCredentialId);
        $credential->setType($source->type);
        $credential->setTransports($source->transports);
        $credential->setAttestationType($source->attestationType);
        $credential->setTrustPath([]);
        $credential->setAaguid($source->aaguid->toBinary());
        $credential->setCredentialPublicKey($source->credentialPublicKey);
        $credential->setUserHandle($source->userHandle);
        $credential->setCounter($source->counter);
        $credential->setOtherUI($source->otherUI);
        $credential->setBackupEligible($source->backupEligible);
        $credential->setBackupStatus($source->backupStatus);
        $credential->setUser($user);
        $credential->setName($name);

        return $credential;
    }
}
