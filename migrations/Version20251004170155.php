<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251004170155 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song ADD added_by_id INT DEFAULT NULL, ADD last_edit_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA155B127A4 FOREIGN KEY (added_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA17A213F93 FOREIGN KEY (last_edit_by_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_33EDEEA155B127A4 ON song (added_by_id)');
        $this->addSql('CREATE INDEX IDX_33EDEEA17A213F93 ON song (last_edit_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA155B127A4');
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA17A213F93');
        $this->addSql('DROP INDEX IDX_33EDEEA155B127A4 ON song');
        $this->addSql('DROP INDEX IDX_33EDEEA17A213F93 ON song');
        $this->addSql('ALTER TABLE song DROP added_by_id, DROP last_edit_by_id');
    }
}
