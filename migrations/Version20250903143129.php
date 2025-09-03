<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903143129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE song (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, preview_url VARCHAR(255) DEFAULT NULL, lyrics LONGTEXT DEFAULT NULL, INDEX IDX_33EDEEA1C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE song_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wedding (id INT AUTO_INCREMENT NOT NULL, marie_id INT DEFAULT NULL, mariee_id INT DEFAULT NULL, date DATE NOT NULL, INDEX IDX_5BC25C96443D8FB7 (marie_id), INDEX IDX_5BC25C96C742AEC6 (mariee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wedding_song (wedding_id INT NOT NULL, song_id INT NOT NULL, INDEX IDX_7A313AA1FCBBB0ED (wedding_id), INDEX IDX_7A313AA1A0BDB2F3 (song_id), PRIMARY KEY(wedding_id, song_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA1C54C8C93 FOREIGN KEY (type_id) REFERENCES song_type (id)');
        $this->addSql('ALTER TABLE wedding ADD CONSTRAINT FK_5BC25C96443D8FB7 FOREIGN KEY (marie_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE wedding ADD CONSTRAINT FK_5BC25C96C742AEC6 FOREIGN KEY (mariee_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE wedding_song ADD CONSTRAINT FK_7A313AA1FCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wedding_song ADD CONSTRAINT FK_7A313AA1A0BDB2F3 FOREIGN KEY (song_id) REFERENCES song (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA1C54C8C93');
        $this->addSql('ALTER TABLE wedding DROP FOREIGN KEY FK_5BC25C96443D8FB7');
        $this->addSql('ALTER TABLE wedding DROP FOREIGN KEY FK_5BC25C96C742AEC6');
        $this->addSql('ALTER TABLE wedding_song DROP FOREIGN KEY FK_7A313AA1FCBBB0ED');
        $this->addSql('ALTER TABLE wedding_song DROP FOREIGN KEY FK_7A313AA1A0BDB2F3');
        $this->addSql('DROP TABLE song');
        $this->addSql('DROP TABLE song_type');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE wedding');
        $this->addSql('DROP TABLE wedding_song');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
