<?php

namespace App\Command;

use App\Service\MsTeamsNotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-teams-notification',
    description: 'Envoie une notification de test vers MS Teams pour vérifier la configuration',
)]
class TestTeamsNotificationCommand extends Command
{
    public function __construct(
        private MsTeamsNotificationService $teamsService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test de notification MS Teams');

        try {
            // Test avec un message simple
            $this->teamsService->sendErrorNotification(
                'Test de notification depuis Notre Messe de Mariage',
                'info',
                [
                    'test' => true,
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
                ]
            );

            $io->success('Notification de test envoyée avec succès !');
            $io->info('Vérifiez votre canal MS Teams pour confirmer la réception.');

            // Test avec une fausse exception
            $io->section('Envoi d\'un test avec exception...');
            
            try {
                throw new \RuntimeException('Ceci est une exception de test pour vérifier le format des notifications');
            } catch (\Throwable $e) {
                $this->teamsService->sendErrorNotification(
                    'Test d\'erreur avec exception',
                    'error',
                    [
                        'test' => true,
                        'url' => 'https://example.com/test',
                        'user_id' => 'TEST_USER',
                        'ip' => '127.0.0.1',
                    ],
                    $e
                );
            }

            $io->success('Notification avec exception envoyée avec succès !');
            
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('Erreur lors de l\'envoi de la notification : ' . $e->getMessage());
            $io->note('Vérifiez que la variable MS_TEAMS_WEBHOOK_URL est correctement configurée dans votre fichier .env');
            
            return Command::FAILURE;
        }
    }
}
