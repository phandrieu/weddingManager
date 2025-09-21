<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250921151127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invitation (id INT AUTO_INCREMENT NOT NULL, wedding_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, used TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F11D61A2FCBBB0ED (wedding_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2FCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id)');
        $this->addSql('ALTER TABLE song CHANGE suggestion suggestion TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2FCBBB0ED');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('ALTER TABLE song CHANGE suggestion suggestion TINYINT(1) DEFAULT 0');
    }
}
