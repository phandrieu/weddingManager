<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203164301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE passkey_credential (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, public_key_credential_id VARBINARY(255) NOT NULL, type VARCHAR(255) NOT NULL, transports JSON NOT NULL, attestation_type VARCHAR(255) NOT NULL, trust_path JSON NOT NULL, aaguid VARBINARY(255) NOT NULL, credential_public_key VARBINARY(255) NOT NULL, user_handle VARBINARY(255) NOT NULL, counter INT NOT NULL, other_ui JSON DEFAULT NULL, backup_eligible TINYINT(1) DEFAULT NULL, backup_status TINYINT(1) DEFAULT NULL, uv_initialized VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_passkey_user (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE passkey_credential ADD CONSTRAINT FK_DFD64A45A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE song_type DROP FOREIGN KEY FK_FF4D81DCB14FF20D');
        $this->addSql('ALTER TABLE song_type ADD CONSTRAINT FK_FF4D81DCB14FF20D FOREIGN KEY (celebration_period_id) REFERENCES celebration_period (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE passkey_credential DROP FOREIGN KEY FK_DFD64A45A76ED395');
        $this->addSql('DROP TABLE passkey_credential');
        $this->addSql('ALTER TABLE song_type DROP FOREIGN KEY FK_FF4D81DCB14FF20D');
        $this->addSql('ALTER TABLE song_type ADD CONSTRAINT FK_FF4D81DCB14FF20D FOREIGN KEY (celebration_period_id) REFERENCES celebration_period (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
