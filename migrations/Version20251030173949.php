<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251030173949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wedding ADD marie_first_name VARCHAR(255) DEFAULT NULL, ADD marie_name VARCHAR(255) DEFAULT NULL, ADD marie_email VARCHAR(255) DEFAULT NULL, ADD marie_telephone VARCHAR(20) DEFAULT NULL, ADD marie_address_line1 VARCHAR(255) DEFAULT NULL, ADD marie_address_line2 VARCHAR(255) DEFAULT NULL, ADD marie_address_postal_code_and_city VARCHAR(255) DEFAULT NULL, ADD mariee_first_name VARCHAR(255) DEFAULT NULL, ADD mariee_name VARCHAR(255) DEFAULT NULL, ADD mariee_email VARCHAR(255) DEFAULT NULL, ADD mariee_telephone VARCHAR(20) DEFAULT NULL, ADD mariee_address_line1 VARCHAR(255) DEFAULT NULL, ADD mariee_address_line2 VARCHAR(255) DEFAULT NULL, ADD mariee_address_postal_code_and_city VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wedding DROP marie_first_name, DROP marie_name, DROP marie_email, DROP marie_telephone, DROP marie_address_line1, DROP marie_address_line2, DROP marie_address_postal_code_and_city, DROP mariee_first_name, DROP mariee_name, DROP mariee_email, DROP mariee_telephone, DROP mariee_address_line1, DROP mariee_address_line2, DROP mariee_address_postal_code_and_city');
    }
}
