<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250904153521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE wedding_user (wedding_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_C44F0249FCBBB0ED (wedding_id), INDEX IDX_C44F0249A76ED395 (user_id), PRIMARY KEY(wedding_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE wedding_user ADD CONSTRAINT FK_C44F0249FCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wedding_user ADD CONSTRAINT FK_C44F0249A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wedding_user DROP FOREIGN KEY FK_C44F0249FCBBB0ED');
        $this->addSql('ALTER TABLE wedding_user DROP FOREIGN KEY FK_C44F0249A76ED395');
        $this->addSql('DROP TABLE wedding_user');
    }
}
