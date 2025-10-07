<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251007202319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, wedding_id INT NOT NULL, song_type_id INT DEFAULT NULL, content VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_9474526CA76ED395 (user_id), INDEX IDX_9474526CFCBBB0ED (wedding_id), INDEX IDX_9474526CC8279DBC (song_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wedding_musicians (wedding_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B22B1DF7FCBBB0ED (wedding_id), INDEX IDX_B22B1DF7A76ED395 (user_id), PRIMARY KEY(wedding_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wedding_parish_users (wedding_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_16D5D124FCBBB0ED (wedding_id), INDEX IDX_16D5D124A76ED395 (user_id), PRIMARY KEY(wedding_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CFCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CC8279DBC FOREIGN KEY (song_type_id) REFERENCES song_type (id)');
        $this->addSql('ALTER TABLE wedding_musicians ADD CONSTRAINT FK_B22B1DF7FCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wedding_musicians ADD CONSTRAINT FK_B22B1DF7A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wedding_parish_users ADD CONSTRAINT FK_16D5D124FCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wedding_parish_users ADD CONSTRAINT FK_16D5D124A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wedding_user DROP FOREIGN KEY FK_C44F0249A76ED395');
        $this->addSql('ALTER TABLE wedding_user DROP FOREIGN KEY FK_C44F0249FCBBB0ED');
        $this->addSql('DROP TABLE wedding_user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE wedding_user (wedding_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_C44F0249FCBBB0ED (wedding_id), INDEX IDX_C44F0249A76ED395 (user_id), PRIMARY KEY(wedding_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE wedding_user ADD CONSTRAINT FK_C44F0249A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wedding_user ADD CONSTRAINT FK_C44F0249FCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CFCBBB0ED');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CC8279DBC');
        $this->addSql('ALTER TABLE wedding_musicians DROP FOREIGN KEY FK_B22B1DF7FCBBB0ED');
        $this->addSql('ALTER TABLE wedding_musicians DROP FOREIGN KEY FK_B22B1DF7A76ED395');
        $this->addSql('ALTER TABLE wedding_parish_users DROP FOREIGN KEY FK_16D5D124FCBBB0ED');
        $this->addSql('ALTER TABLE wedding_parish_users DROP FOREIGN KEY FK_16D5D124A76ED395');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE wedding_musicians');
        $this->addSql('DROP TABLE wedding_parish_users');
    }
}
