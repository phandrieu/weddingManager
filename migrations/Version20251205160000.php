<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251205160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Restore song.private_to_wedding_id relation and drop the temporary boolean column.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
    SET @song_private_to_wedding_id_exists := (
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'song'
          AND COLUMN_NAME = 'private_to_wedding_id'
    );
    SQL);

        $this->addSql(<<<'SQL'
    SET @ddl_add_private_to_wedding_id := IF(
        @song_private_to_wedding_id_exists = 0,
        'ALTER TABLE song ADD COLUMN private_to_wedding_id INT DEFAULT NULL',
        'SELECT 1'
    );
    SQL);
        $this->addSql('PREPARE stmt FROM @ddl_add_private_to_wedding_id');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');

        $this->addSql('ALTER TABLE song MODIFY COLUMN private_to_wedding_id INT DEFAULT NULL');

        $this->addSql(<<<'SQL'
    SET @song_private_flag_exists := (
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'song'
          AND COLUMN_NAME = 'private_to_wedding'
    );
    SQL);

        $this->addSql(<<<"SQL"
    SET @ddl_update_private_to_wedding := IF(
        @song_private_flag_exists = 1,
        'UPDATE song s LEFT JOIN (SELECT sw.song_id, MIN(sw.wedding_id) AS wedding_id FROM wedding_song sw GROUP BY sw.song_id) AS lookup ON lookup.song_id = s.id SET s.private_to_wedding_id = lookup.wedding_id WHERE s.private_to_wedding_id IS NULL AND lookup.wedding_id IS NOT NULL AND s.private_to_wedding = 1',
        'SELECT 1'
    );
    SQL);
        $this->addSql('PREPARE stmt FROM @ddl_update_private_to_wedding');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');

        $this->addSql(<<<'SQL'
    SET @fk_private_to_wedding_exists := (
        SELECT COUNT(*)
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = 'song'
          AND CONSTRAINT_NAME = 'FK_SONG_PRIVATE_TO_WEDDING'
    );
    SQL);
        $this->addSql(<<<'SQL'
    SET @ddl_drop_fk_private_to_wedding := IF(
        @fk_private_to_wedding_exists = 1,
        'ALTER TABLE song DROP FOREIGN KEY FK_SONG_PRIVATE_TO_WEDDING',
        'SELECT 1'
    );
    SQL);
        $this->addSql('PREPARE stmt FROM @ddl_drop_fk_private_to_wedding');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');

        $this->addSql(<<<'SQL'
    SET @idx_private_to_wedding_exists := (
        SELECT COUNT(*)
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'song'
          AND INDEX_NAME = 'IDX_SONG_PRIVATE_TO_WEDDING'
    );
    SQL);
        $this->addSql(<<<'SQL'
    SET @ddl_drop_idx_private_to_wedding := IF(
        @idx_private_to_wedding_exists = 1,
        'DROP INDEX IDX_SONG_PRIVATE_TO_WEDDING ON song',
        'SELECT 1'
    );
    SQL);
        $this->addSql('PREPARE stmt FROM @ddl_drop_idx_private_to_wedding');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');

        $this->addSql('CREATE INDEX IDX_SONG_PRIVATE_TO_WEDDING ON song (private_to_wedding_id)');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_SONG_PRIVATE_TO_WEDDING FOREIGN KEY (private_to_wedding_id) REFERENCES wedding (id) ON DELETE SET NULL');

        $this->addSql(<<<'SQL'
    SET @ddl_drop_private_flag_column := IF(
        @song_private_flag_exists = 1,
        'ALTER TABLE song DROP COLUMN private_to_wedding',
        'SELECT 1'
    );
    SQL);
        $this->addSql('PREPARE stmt FROM @ddl_drop_private_flag_column');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE song ADD COLUMN private_to_wedding TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE song SET private_to_wedding = 1 WHERE private_to_wedding_id IS NOT NULL');
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_SONG_PRIVATE_TO_WEDDING');
        $this->addSql('DROP INDEX IDX_SONG_PRIVATE_TO_WEDDING ON song');
        $this->addSql('ALTER TABLE song DROP COLUMN private_to_wedding_id');
    }
}
