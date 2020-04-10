<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200410125625 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('ALTER TABLE project ADD COLUMN has_been_read BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__project AS SELECT id, url, should_bid, posted_at, client_name, client_review_count, client_review_rating, title, description, platform, location, raw_html FROM project');
        $this->addSql('DROP TABLE project');
        $this->addSql('CREATE TABLE project (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, url CLOB NOT NULL, should_bid BOOLEAN NOT NULL, posted_at DATETIME DEFAULT NULL, client_name VARCHAR(255) DEFAULT NULL, client_review_count INTEGER DEFAULT NULL, client_review_rating INTEGER DEFAULT NULL, title CLOB NOT NULL, description CLOB NOT NULL, platform VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, raw_html CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO project (id, url, should_bid, posted_at, client_name, client_review_count, client_review_rating, title, description, platform, location, raw_html) SELECT id, url, should_bid, posted_at, client_name, client_review_count, client_review_rating, title, description, platform, location, raw_html FROM __temp__project');
        $this->addSql('DROP TABLE __temp__project');
    }
}
