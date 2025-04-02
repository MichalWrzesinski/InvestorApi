<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250402082424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add column default_quote_symbol_id to User';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD default_quote_symbol_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN "user".default_quote_symbol_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649DF4367D2 FOREIGN KEY (default_quote_symbol_id) REFERENCES symbol (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8D93D649DF4367D2 ON "user" (default_quote_symbol_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649DF4367D2');
        $this->addSql('DROP INDEX IDX_8D93D649DF4367D2');
        $this->addSql('ALTER TABLE "user" DROP default_quote_symbol_id');
    }
}
