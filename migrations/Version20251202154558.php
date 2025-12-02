<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251202154558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE celebration_period (id INT AUTO_INCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, period_order INT DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP INDEX UNIQ_INVITATION_TOKEN ON invitation');
        $this->addSql('ALTER TABLE song_type ADD celebration_period_id INT DEFAULT NULL, DROP celebration_period');
        $this->addSql('ALTER TABLE song_type ADD CONSTRAINT FK_FF4D81DCB14FF20D FOREIGN KEY (celebration_period_id) REFERENCES celebration_period (id)');
        $this->addSql('CREATE INDEX IDX_FF4D81DCB14FF20D ON song_type (celebration_period_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song_type DROP FOREIGN KEY FK_FF4D81DCB14FF20D');
        $this->addSql('DROP TABLE celebration_period');
        $this->addSql('ALTER TABLE comment CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_INVITATION_TOKEN ON invitation (token)');
        $this->addSql('DROP INDEX IDX_FF4D81DCB14FF20D ON song_type');
        $this->addSql('ALTER TABLE song_type ADD celebration_period VARCHAR(255) DEFAULT NULL, DROP celebration_period_id');
    }
}
