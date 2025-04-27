<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250423235643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the log_entries table';
    }

    public function up(Schema $schema): void
    {
        // Create the log_entries table
        $this->addSql(<<<'SQL'
            CREATE TABLE log_entries (
                id INT AUTO_INCREMENT NOT NULL, 
                uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)', 
                service VARCHAR(100) NOT NULL, 
                start_date DATETIME NOT NULL, 
                end_date DATETIME NOT NULL, 
                method VARCHAR(10) NOT NULL, 
                path VARCHAR(255) NOT NULL, 
                status_code INT NOT NULL, 
                UNIQUE INDEX UNIQ_15358B52D17F50A6 (uuid), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Drop the log_entries table
        $this->addSql(<<<'SQL'
            DROP TABLE log_entries;
        SQL);
    }
}
