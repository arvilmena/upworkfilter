<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200410103704 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__scrape AS SELECT id, hostname, url, type, status_code, body, crawled_at, scrape_id FROM scrape');
        $this->addSql('DROP TABLE scrape');
        $this->addSql('CREATE TABLE scrape (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, hostname VARCHAR(255) NOT NULL COLLATE BINARY, url CLOB NOT NULL COLLATE BINARY, type VARCHAR(255) NOT NULL COLLATE BINARY, body CLOB NOT NULL COLLATE BINARY, crawled_at DATETIME NOT NULL, scrape_id VARCHAR(255) DEFAULT NULL COLLATE BINARY, status_code INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO scrape (id, hostname, url, type, status_code, body, crawled_at, scrape_id) SELECT id, hostname, url, type, status_code, body, crawled_at, scrape_id FROM __temp__scrape');
        $this->addSql('DROP TABLE __temp__scrape');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__scrape AS SELECT id, hostname, url, type, status_code, body, crawled_at, scrape_id FROM scrape');
        $this->addSql('DROP TABLE scrape');
        $this->addSql('CREATE TABLE scrape (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, hostname VARCHAR(255) NOT NULL, url CLOB NOT NULL, type VARCHAR(255) NOT NULL, body CLOB NOT NULL, crawled_at DATETIME NOT NULL, scrape_id VARCHAR(255) DEFAULT NULL, status_code VARCHAR(255) DEFAULT NULL COLLATE BINARY)');
        $this->addSql('INSERT INTO scrape (id, hostname, url, type, status_code, body, crawled_at, scrape_id) SELECT id, hostname, url, type, status_code, body, crawled_at, scrape_id FROM __temp__scrape');
        $this->addSql('DROP TABLE __temp__scrape');
    }
}
