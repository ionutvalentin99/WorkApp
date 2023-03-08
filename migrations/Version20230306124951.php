<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230306124951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE concedii DROP FOREIGN KEY FK_69A34A55A76ED395');
        $this->addSql('ALTER TABLE concedii ADD CONSTRAINT FK_69A34A55A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE pontaje ADD date DATE DEFAULT NULL, ADD time_start TIME DEFAULT NULL, ADD time_end TIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE concedii DROP FOREIGN KEY FK_69A34A55A76ED395');
        $this->addSql('ALTER TABLE concedii ADD CONSTRAINT FK_69A34A55A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pontaje DROP date, DROP time_start, DROP time_end');
    }
}
