<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20250330082544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates a materialized view exchange_rate latest with literal symbols';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
                CREATE MATERIALIZED VIEW exchange_rate_latest AS
                SELECT DISTINCT ON (b.symbol, q.symbol)
                    er.id,
                    b.symbol AS base_symbol,
                    q.symbol AS quote_symbol,
                    er.price
                FROM exchange_rate er
                JOIN symbol b ON er.base_id = b.id
                JOIN symbol q ON er.quote_id = q.id
                WHERE er.deleted_at IS NULL
                ORDER BY b.symbol, q.symbol, er.created_at DESC;
            SQL
        );

        $this->addSql('CREATE UNIQUE INDEX exchange_rate_latest_unique ON exchange_rate_latest (base_symbol, quote_symbol)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP MATERIALIZED VIEW IF EXISTS exchange_rate_latest');
    }
}
