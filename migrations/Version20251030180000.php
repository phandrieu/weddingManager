<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter ON DELETE CASCADE sur la relation invitation -> wedding
 */
final class Version20251030180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de ON DELETE CASCADE sur la clé étrangère wedding_id de la table invitation';
    }

    public function up(Schema $schema): void
    {
        // Supprimer l'ancienne contrainte
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2FCBBB0ED');
        
        // Recréer la contrainte avec ON DELETE CASCADE
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2FCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Supprimer la contrainte avec CASCADE
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2FCBBB0ED');
        
        // Recréer la contrainte sans ON DELETE CASCADE (comportement par défaut)
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2FCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id)');
    }
}
