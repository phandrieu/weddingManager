<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251106181534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wedding DROP FOREIGN KEY FK_5BC25C96443D8FB7');
        $this->addSql('ALTER TABLE wedding DROP FOREIGN KEY FK_5BC25C96C742AEC6');
        $this->addSql('ALTER TABLE wedding ADD CONSTRAINT FK_5BC25C96443D8FB7 FOREIGN KEY (marie_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE wedding ADD CONSTRAINT FK_5BC25C96C742AEC6 FOREIGN KEY (mariee_id) REFERENCES `user` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wedding DROP FOREIGN KEY FK_5BC25C96443D8FB7');
        $this->addSql('ALTER TABLE wedding DROP FOREIGN KEY FK_5BC25C96C742AEC6');
        $this->addSql('ALTER TABLE wedding ADD CONSTRAINT FK_5BC25C96443D8FB7 FOREIGN KEY (marie_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE wedding ADD CONSTRAINT FK_5BC25C96C742AEC6 FOREIGN KEY (mariee_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
