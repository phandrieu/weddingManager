<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/*
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250926210544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wedding ADD montant_total DOUBLE PRECISION NOT NULL DEFAULT 0, ADD montant_paye DOUBLE PRECISION NOT NULL DEFAULT 0, CHANGE archive archive TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wedding DROP montant_total, DROP montant_paye, CHANGE archive archive TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
