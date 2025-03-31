<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250331184030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user_asset_operation table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE user_asset_operation (
                id UUID NOT NULL,
                user_asset_id UUID NOT NULL,
                amount DOUBLE PRECISION NOT NULL,
                type VARCHAR(255) NOT NULL,
                deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql('CREATE INDEX IDX_E66722D8E557FD41 ON user_asset_operation (user_asset_id)');

        $this->addSql('COMMENT ON COLUMN user_asset_operation.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_asset_operation.user_asset_id IS \'(DC2Type:uuid)\'');

        $this->addSql('COMMENT ON COLUMN user_asset_operation.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_asset_operation.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_asset_operation.updated_at IS \'(DC2Type:datetime_immutable)\'');

        $this->addSql('ALTER TABLE user_asset_operation ADD CONSTRAINT FK_E66722D8E557FD41 FOREIGN KEY (user_asset_id) REFERENCES user_asset (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_asset_operation DROP CONSTRAINT FK_E66722D8E557FD41');
        $this->addSql('DROP TABLE user_asset_operation');
    }
}
