<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Modification de la contrainte de clé étrangère song_id dans wedding_song_selection
 * pour permettre la suppression de chants (SET NULL au lieu de RESTRICT)
 */
final class Version20251204091900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change wedding_song_selection.song_id foreign key to SET NULL on delete';
    }

    public function up(Schema $schema): void
    {
        // Supprimer l'ancienne contrainte
        $this->addSql('ALTER TABLE wedding_song_selection DROP FOREIGN KEY FK_6F5BEFE3A0BDB2F3');
        
        // Recréer la contrainte avec ON DELETE SET NULL
        $this->addSql('ALTER TABLE wedding_song_selection ADD CONSTRAINT FK_6F5BEFE3A0BDB2F3 FOREIGN KEY (song_id) REFERENCES song (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // Supprimer la contrainte SET NULL
        $this->addSql('ALTER TABLE wedding_song_selection DROP FOREIGN KEY FK_6F5BEFE3A0BDB2F3');
        
        // Recréer l'ancienne contrainte sans ON DELETE
        $this->addSql('ALTER TABLE wedding_song_selection ADD CONSTRAINT FK_6F5BEFE3A0BDB2F3 FOREIGN KEY (song_id) REFERENCES song (id)');
    }
}
