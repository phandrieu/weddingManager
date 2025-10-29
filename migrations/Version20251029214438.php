<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251029214438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, invitation_id INT DEFAULT NULL, comment_id INT DEFAULT NULL, wedding_id INT DEFAULT NULL, type VARCHAR(50) NOT NULL, message LONGTEXT NOT NULL, link VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, is_read TINYINT(1) NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), INDEX IDX_BF5476CAA35D7AF0 (invitation_id), INDEX IDX_BF5476CAF8697D13 (comment_id), INDEX IDX_BF5476CAFCBBB0ED (wedding_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA35D7AF0 FOREIGN KEY (invitation_id) REFERENCES invitation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAF8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAFCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wedding_song_selection RENAME INDEX idx_6f5befe3fcbbb0ed TO IDX_27C35AEFFCBBB0ED');
        $this->addSql('ALTER TABLE wedding_song_selection RENAME INDEX idx_6f5befe3b336a438 TO IDX_27C35AEFC8279DBC');
        $this->addSql('ALTER TABLE wedding_song_selection RENAME INDEX idx_6f5befe3a0bdb2f3 TO IDX_27C35AEFA0BDB2F3');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA35D7AF0');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAF8697D13');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAFCBBB0ED');
        $this->addSql('DROP TABLE notification');
        $this->addSql('ALTER TABLE wedding_song_selection RENAME INDEX idx_27c35aeffcbbb0ed TO IDX_6F5BEFE3FCBBB0ED');
        $this->addSql('ALTER TABLE wedding_song_selection RENAME INDEX idx_27c35aefa0bdb2f3 TO IDX_6F5BEFE3A0BDB2F3');
        $this->addSql('ALTER TABLE wedding_song_selection RENAME INDEX idx_27c35aefc8279dbc TO IDX_6F5BEFE3B336A438');
    }
}
