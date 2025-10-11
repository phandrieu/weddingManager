<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251011155448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Création de la table pivot
        $this->addSql('CREATE TABLE song_song_type (song_id INT NOT NULL, song_type_id INT NOT NULL, INDEX IDX_87E19428A0BDB2F3 (song_id), INDEX IDX_87E19428C8279DBC (song_type_id), PRIMARY KEY(song_id, song_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE song_song_type ADD CONSTRAINT FK_87E19428A0BDB2F3 FOREIGN KEY (song_id) REFERENCES song (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE song_song_type ADD CONSTRAINT FK_87E19428C8279DBC FOREIGN KEY (song_type_id) REFERENCES song_type (id) ON DELETE CASCADE');

        // ⚠️ Étape importante :
        // Avant de supprimer la colonne, on copie les valeurs de song.type_id dans la table pivot
        $this->addSql('INSERT INTO song_song_type (song_id, song_type_id)
                   SELECT id, type_id FROM song WHERE type_id IS NOT NULL');

        // Puis suppression de l’ancienne FK et colonne
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA1C54C8C93');
        $this->addSql('DROP INDEX IDX_33EDEEA1C54C8C93 ON song');
        $this->addSql('ALTER TABLE song DROP type_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song_song_type DROP FOREIGN KEY FK_87E19428A0BDB2F3');
        $this->addSql('ALTER TABLE song_song_type DROP FOREIGN KEY FK_87E19428C8279DBC');
        $this->addSql('DROP TABLE song_song_type');
        $this->addSql('ALTER TABLE song ADD type_id INT NOT NULL');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA1C54C8C93 FOREIGN KEY (type_id) REFERENCES song_type (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_33EDEEA1C54C8C93 ON song (type_id)');
    }
}
