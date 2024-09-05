<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240905232655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE pontaje SET company_id = NULL WHERE company_id IS NOT NULL AND company_id NOT IN (SELECT id FROM company)');
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pontaje ADD CONSTRAINT FK_A126FBD0979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_A126FBD0979B1AD6 ON pontaje (company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pontaje DROP FOREIGN KEY FK_A126FBD0979B1AD6');
        $this->addSql('DROP INDEX IDX_A126FBD0979B1AD6 ON pontaje');
    }
}
