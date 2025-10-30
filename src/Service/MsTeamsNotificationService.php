<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class MsTeamsNotificationService
{
    private const MAX_MESSAGE_LENGTH = 28000; // Teams a une limite de caractères

    public function __construct(
        private HttpClientInterface $httpClient,
        private ?string $webhookUrl,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Envoie une notification d'erreur vers MS Teams
     */
    public function sendErrorNotification(
        string $message,
        string $level,
        array $context = [],
        ?\Throwable $exception = null
    ): void {
        if (!$this->webhookUrl) {
            $this->logger->warning('MS Teams webhook URL is not configured');
            return;
        }

        try {
            $card = $this->buildAdaptiveCard($message, $level, $context, $exception);
            
            $this->httpClient->request('POST', $this->webhookUrl, [
                'json' => $card,
                'timeout' => 5,
            ]);
        } catch (\Throwable $e) {
            // Ne pas faire planter l'application si l'envoi vers Teams échoue
            $this->logger->error('Failed to send notification to MS Teams', [
                'error' => $e->getMessage(),
                'original_message' => $message,
            ]);
        }
    }

    /**
     * Construit une Adaptive Card pour MS Teams
     */
    private function buildAdaptiveCard(
        string $message,
        string $level,
        array $context,
        ?\Throwable $exception
    ): array {
        $color = $this->getColorForLevel($level);
        $icon = $this->getIconForLevel($level);
        
        $facts = [
            [
                'title' => '🔔 Niveau',
                'value' => strtoupper($level),
            ],
            [
                'title' => '⏰ Date/Heure',
                'value' => (new \DateTime())->format('d/m/Y H:i:s'),
            ],
            [
                'title' => '🌐 Environnement',
                'value' => $_ENV['APP_ENV'] ?? 'production',
            ],
        ];

        if (!empty($context['url'] ?? null)) {
            $facts[] = [
                'title' => '🔗 URL',
                'value' => $context['url'],
            ];
        }

        if (!empty($context['user_id'] ?? null)) {
            $facts[] = [
                'title' => '👤 Utilisateur',
                'value' => sprintf('ID: %s', $context['user_id']),
            ];
        }

        if (!empty($context['ip'] ?? null)) {
            $facts[] = [
                'title' => '🌍 IP',
                'value' => $context['ip'],
            ];
        }

        $body = [
            [
                'type' => 'TextBlock',
                'text' => $icon . ' Erreur détectée sur Notre Messe de Mariage',
                'weight' => 'Bolder',
                'size' => 'Large',
                'color' => $color,
            ],
            [
                'type' => 'TextBlock',
                'text' => $this->truncateMessage($message),
                'wrap' => true,
                'spacing' => 'Medium',
            ],
            [
                'type' => 'FactSet',
                'facts' => $facts,
                'spacing' => 'Medium',
            ],
        ];

        // Ajout des détails de l'exception
        if ($exception) {
            $body[] = [
                'type' => 'TextBlock',
                'text' => '🐛 Détails de l\'exception',
                'weight' => 'Bolder',
                'spacing' => 'Medium',
            ];
            
            $body[] = [
                'type' => 'TextBlock',
                'text' => sprintf(
                    "**Type:** %s\n\n**Fichier:** %s:%d\n\n**Trace:**\n```\n%s\n```",
                    get_class($exception),
                    $exception->getFile(),
                    $exception->getLine(),
                    $this->truncateMessage($exception->getTraceAsString(), 2000)
                ),
                'wrap' => true,
                'fontType' => 'Monospace',
                'size' => 'Small',
            ];
        }

        return [
            'type' => 'message',
            'attachments' => [
                [
                    'contentType' => 'application/vnd.microsoft.card.adaptive',
                    'contentUrl' => null,
                    'content' => [
                        '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                        'type' => 'AdaptiveCard',
                        'version' => '1.4',
                        'body' => $body,
                        'msteams' => [
                            'width' => 'Full',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getColorForLevel(string $level): string
    {
        return match (strtolower($level)) {
            'emergency', 'alert', 'critical' => 'Attention',
            'error' => 'Warning',
            'warning' => 'Warning',
            'notice', 'info' => 'Accent',
            default => 'Default',
        };
    }

    private function getIconForLevel(string $level): string
    {
        return match (strtolower($level)) {
            'emergency', 'alert', 'critical' => '🚨',
            'error' => '❌',
            'warning' => '⚠️',
            'notice' => '📢',
            'info' => 'ℹ️',
            default => '📝',
        };
    }

    private function truncateMessage(string $message, ?int $maxLength = null): string
    {
        $maxLength = $maxLength ?? self::MAX_MESSAGE_LENGTH;
        
        if (mb_strlen($message) <= $maxLength) {
            return $message;
        }

        return mb_substr($message, 0, $maxLength - 3) . '...';
    }
}
