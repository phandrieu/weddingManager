<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Renforce l'intégrité des invitations et normalise les booléens clés.
 */
final class Version20251202124500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Invitation token unique + contraintes NOT NULL/DEFAULT sur plusieurs booléens.';
    }

    public function up(Schema $schema): void
    {
        // Invitation: FK stricte, default used, token unique
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2FCBBB0ED');
        $this->addSql('ALTER TABLE invitation CHANGE wedding_id wedding_id INT NOT NULL, CHANGE used used TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2FCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_INVITATION_TOKEN ON invitation (token)');

        // Songs: booléens stricts
        $this->addSql('ALTER TABLE song CHANGE suggestion suggestion TINYINT(1) DEFAULT 0 NOT NULL, CHANGE song song TINYINT(1) DEFAULT 1 NOT NULL');

        // Users: abonnement explicite
        $this->addSql('ALTER TABLE `user` CHANGE subscription subscription TINYINT(1) DEFAULT 0 NOT NULL');

        // Weddings: valeurs par défaut pour les statuts booléens
        $this->addSql('ALTER TABLE wedding CHANGE archive archive TINYINT(1) DEFAULT 0 NOT NULL, CHANGE messe messe TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Wedding booléens sans défaut
        $this->addSql('ALTER TABLE wedding CHANGE archive archive TINYINT(1) NOT NULL, CHANGE messe messe TINYINT(1) NOT NULL');

        // User subscription
        $this->addSql('ALTER TABLE `user` CHANGE subscription subscription TINYINT(1) NOT NULL');

        // Song flags
        $this->addSql('ALTER TABLE song CHANGE suggestion suggestion TINYINT(1) DEFAULT NULL, CHANGE song song TINYINT(1) NOT NULL');

        // Invitation tokens, FK et booléen
        $this->addSql('DROP INDEX UNIQ_INVITATION_TOKEN ON invitation');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2FCBBB0ED');
        $this->addSql('ALTER TABLE invitation CHANGE wedding_id wedding_id INT DEFAULT NULL, CHANGE used used TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2FCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id)');
    }
}
