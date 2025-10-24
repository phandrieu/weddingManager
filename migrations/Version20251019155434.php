<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251019155434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD credits INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE wedding ADD created_by_id INT DEFAULT NULL, ADD created_with_credit TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE wedding ADD CONSTRAINT FK_5BC25C96B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_5BC25C96B03A8386 ON wedding (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP credits');
        $this->addSql('ALTER TABLE wedding DROP FOREIGN KEY FK_5BC25C96B03A8386');
        $this->addSql('DROP INDEX IDX_5BC25C96B03A8386 ON wedding');
        $this->addSql('ALTER TABLE wedding DROP created_by_id, DROP created_with_credit');
    }
}
