<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250926214649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wedding ADD messe TINYINT(1) NOT NULL DEFAULT 1, CHANGE archive archive TINYINT(1) NOT NULL, CHANGE montant_total montant_total DOUBLE PRECISION NOT NULL, CHANGE montant_paye montant_paye DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wedding DROP messe, CHANGE archive archive TINYINT(1) DEFAULT 0 NOT NULL, CHANGE montant_total montant_total DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE montant_paye montant_paye DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    }
}
