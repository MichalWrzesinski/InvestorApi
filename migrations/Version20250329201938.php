<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250329201938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create symbol table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
                CREATE TABLE symbol (
                    id UUID NOT NULL,
                    symbol VARCHAR(15) NOT NULL,
                    name VARCHAR(50) NOT NULL,
                    type VARCHAR(20) NOT NULL,
                    processor VARCHAR(20) NOT NULL,
                    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    PRIMARY KEY(id)
                )
            SQL
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ECC836F9ECC836F9 ON symbol (symbol)');
        $this->addSql('COMMENT ON COLUMN symbol.id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE symbol');
    }
}
