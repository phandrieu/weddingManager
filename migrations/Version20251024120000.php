<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251024120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le suivi de paiement des mariés sur les mariages.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE wedding ADD requires_couple_payment TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE wedding DROP requires_couple_payment');
    }
}
