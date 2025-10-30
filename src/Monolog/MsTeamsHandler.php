<?php

namespace App\Monolog;

use App\Service\MsTeamsNotificationService;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;

class MsTeamsHandler extends AbstractProcessingHandler
{
    public function __construct(
        private MsTeamsNotificationService $teamsService,
        private RequestStack $requestStack,
        private ?Security $security = null,
        int|string|Level $level = Level::Error,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        $context = $record->context;
        
        // Enrichir le contexte avec des informations de la requête
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $context['url'] = $request->getUri();
            $context['method'] = $request->getMethod();
            $context['ip'] = $request->getClientIp();
            $context['user_agent'] = $request->headers->get('User-Agent');
        }

        // Ajouter l'utilisateur connecté
        if ($this->security && $user = $this->security->getUser()) {
            $context['user_id'] = method_exists($user, 'getId') ? $user->getId() : $user->getUserIdentifier();
            $context['user_email'] = $user->getUserIdentifier();
        }

        // Extraire l'exception si disponible
        $exception = $context['exception'] ?? null;

        $this->teamsService->sendErrorNotification(
            $record->message,
            $record->level->getName(),
            $context,
            $exception
        );
    }
}
