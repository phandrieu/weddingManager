<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251028123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la table wedding_song_selection pour suivre les validations du déroulé.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE wedding_song_selection (id INT AUTO_INCREMENT NOT NULL, wedding_id INT NOT NULL, song_type_id INT NOT NULL, song_id INT DEFAULT NULL, validated_by_musician TINYINT(1) DEFAULT 0 NOT NULL, validated_by_parish TINYINT(1) DEFAULT 0 NOT NULL, UNIQUE INDEX uniq_wedding_type (wedding_id, song_type_id), INDEX IDX_6F5BEFE3FCBBB0ED (wedding_id), INDEX IDX_6F5BEFE3B336A438 (song_type_id), INDEX IDX_6F5BEFE3A0BDB2F3 (song_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE wedding_song_selection ADD CONSTRAINT FK_6F5BEFE3FCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wedding_song_selection ADD CONSTRAINT FK_6F5BEFE3B336A438 FOREIGN KEY (song_type_id) REFERENCES song_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wedding_song_selection ADD CONSTRAINT FK_6F5BEFE3A0BDB2F3 FOREIGN KEY (song_id) REFERENCES song (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE wedding_song_selection DROP FOREIGN KEY FK_6F5BEFE3FCBBB0ED');
        $this->addSql('ALTER TABLE wedding_song_selection DROP FOREIGN KEY FK_6F5BEFE3B336A438');
        $this->addSql('ALTER TABLE wedding_song_selection DROP FOREIGN KEY FK_6F5BEFE3A0BDB2F3');
        $this->addSql('DROP TABLE wedding_song_selection');
    }
}
