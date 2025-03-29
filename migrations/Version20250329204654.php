<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250329204654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create exchange_rate table with UUID PK, base/quote relations to symbol and timestamp/soft delete support';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
                CREATE TABLE exchange_rate (
                    id UUID NOT NULL,
                    base_id UUID NOT NULL,
                    quote_id UUID NOT NULL,
                    price DOUBLE PRECISION NOT NULL,
                    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    PRIMARY KEY(id)
                )
            SQL
        );
        $this->addSql('CREATE INDEX IDX_E9521FAB6967DF41 ON exchange_rate (base_id)');
        $this->addSql('CREATE INDEX IDX_E9521FABDB805178 ON exchange_rate (quote_id)');
        $this->addSql('COMMENT ON COLUMN exchange_rate.base_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN exchange_rate.quote_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE exchange_rate ADD CONSTRAINT FK_E9521FAB6967DF41 FOREIGN KEY (base_id) REFERENCES symbol (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE exchange_rate ADD CONSTRAINT FK_E9521FABDB805178 FOREIGN KEY (quote_id) REFERENCES symbol (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE exchange_rate DROP CONSTRAINT FK_E9521FAB6967DF41');
        $this->addSql('ALTER TABLE exchange_rate DROP CONSTRAINT FK_E9521FABDB805178');
        $this->addSql('DROP TABLE exchange_rate');
    }
}
