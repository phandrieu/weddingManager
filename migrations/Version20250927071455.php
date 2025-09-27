<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250927071455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wedding ADD priest_first_name VARCHAR(255) DEFAULT NULL, ADD priest_last_name VARCHAR(255) DEFAULT NULL, ADD priest_phone_number VARCHAR(20) DEFAULT NULL, ADD priest_email VARCHAR(255) DEFAULT NULL, ADD time TIME DEFAULT NULL, CHANGE messe messe TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wedding DROP priest_first_name, DROP priest_last_name, DROP priest_phone_number, DROP priest_email, DROP time, CHANGE messe messe TINYINT(1) DEFAULT 1 NOT NULL');
    }
}
