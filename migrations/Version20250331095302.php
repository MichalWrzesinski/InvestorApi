<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250331095302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
                CREATE TABLE user_asset (
                    id UUID NOT NULL,
                    user_id INT NOT NULL,
                    symbol_id UUID NOT NULL,
                    balance DOUBLE PRECISION NOT NULL,
                    name VARCHAR(100) NOT NULL,
                    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    PRIMARY KEY(id)
                )
            SQL
        );
        $this->addSql('CREATE INDEX IDX_E06DA104A76ED395 ON user_asset (user_id)');
        $this->addSql('CREATE INDEX IDX_E06DA104C0F75674 ON user_asset (symbol_id)');
        $this->addSql('COMMENT ON COLUMN user_asset.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_asset.symbol_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_asset.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_asset.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_asset.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user_asset ADD CONSTRAINT FK_E06DA104A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_asset ADD CONSTRAINT FK_E06DA104C0F75674 FOREIGN KEY (symbol_id) REFERENCES symbol (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE exchange_rate ALTER id TYPE UUID');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_asset DROP CONSTRAINT FK_E06DA104A76ED395');
        $this->addSql('ALTER TABLE user_asset DROP CONSTRAINT FK_E06DA104C0F75674');
        $this->addSql('DROP TABLE user_asset');
    }
}
