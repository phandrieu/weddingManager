<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250913114148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_repertoire (user_id INT NOT NULL, song_id INT NOT NULL, INDEX IDX_9B51BE9AA76ED395 (user_id), INDEX IDX_9B51BE9AA0BDB2F3 (song_id), PRIMARY KEY(user_id, song_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_repertoire ADD CONSTRAINT FK_9B51BE9AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_repertoire ADD CONSTRAINT FK_9B51BE9AA0BDB2F3 FOREIGN KEY (song_id) REFERENCES song (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user CHANGE subscription subscription TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_repertoire DROP FOREIGN KEY FK_9B51BE9AA76ED395');
        $this->addSql('ALTER TABLE user_repertoire DROP FOREIGN KEY FK_9B51BE9AA0BDB2F3');
        $this->addSql('DROP TABLE user_repertoire');
        $this->addSql('ALTER TABLE `user` CHANGE subscription subscription TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
