<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251004081654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song ADD lyrics_author_name VARCHAR(255) DEFAULT NULL, ADD music_author_name VARCHAR(255) DEFAULT NULL, ADD editor_name VARCHAR(255) DEFAULT NULL, ADD interpret_name VARCHAR(255) DEFAULT NULL, ADD text_ref VARCHAR(255) DEFAULT NULL, ADD text_translation_name VARCHAR(255) DEFAULT NULL, CHANGE song song TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song DROP lyrics_author_name, DROP music_author_name, DROP editor_name, DROP interpret_name, DROP text_ref, DROP text_translation_name, CHANGE song song TINYINT(1) DEFAULT 1 NOT NULL');
    }
}
