<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200410160842 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__project AS SELECT id, url, should_bid, posted_at, client_name, client_review_count, client_review_rating, title, description, platform, location, raw_html, has_been_read, has_been_read_at, cost FROM project');
        $this->addSql('DROP TABLE project');
        $this->addSql('CREATE TABLE project (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, url CLOB NOT NULL COLLATE BINARY, should_bid BOOLEAN NOT NULL, posted_at DATETIME DEFAULT NULL, client_name VARCHAR(255) DEFAULT NULL COLLATE BINARY, client_review_count INTEGER DEFAULT NULL, client_review_rating INTEGER DEFAULT NULL, title CLOB NOT NULL COLLATE BINARY, description CLOB NOT NULL COLLATE BINARY, platform VARCHAR(255) DEFAULT NULL COLLATE BINARY, location VARCHAR(255) DEFAULT NULL COLLATE BINARY, raw_html CLOB DEFAULT NULL COLLATE BINARY, has_been_read BOOLEAN DEFAULT NULL, has_been_read_at DATETIME DEFAULT NULL, budget VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO project (id, url, should_bid, posted_at, client_name, client_review_count, client_review_rating, title, description, platform, location, raw_html, has_been_read, has_been_read_at, budget) SELECT id, url, should_bid, posted_at, client_name, client_review_count, client_review_rating, title, description, platform, location, raw_html, has_been_read, has_been_read_at, cost FROM __temp__project');
        $this->addSql('DROP TABLE __temp__project');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__project AS SELECT id, url, should_bid, posted_at, client_name, client_review_count, client_review_rating, title, description, platform, location, raw_html, has_been_read, has_been_read_at, budget FROM project');
        $this->addSql('DROP TABLE project');
        $this->addSql('CREATE TABLE project (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, url CLOB NOT NULL, should_bid BOOLEAN NOT NULL, posted_at DATETIME DEFAULT NULL, client_name VARCHAR(255) DEFAULT NULL, client_review_count INTEGER DEFAULT NULL, client_review_rating INTEGER DEFAULT NULL, title CLOB NOT NULL, description CLOB NOT NULL, platform VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, raw_html CLOB DEFAULT NULL, has_been_read BOOLEAN DEFAULT NULL, has_been_read_at DATETIME DEFAULT NULL, cost VARCHAR(255) DEFAULT NULL COLLATE BINARY)');
        $this->addSql('INSERT INTO project (id, url, should_bid, posted_at, client_name, client_review_count, client_review_rating, title, description, platform, location, raw_html, has_been_read, has_been_read_at, cost) SELECT id, url, should_bid, posted_at, client_name, client_review_count, client_review_rating, title, description, platform, location, raw_html, has_been_read, has_been_read_at, budget FROM __temp__project');
        $this->addSql('DROP TABLE __temp__project');
    }
}
