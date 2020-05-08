<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200508154903 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, url LONGTEXT NOT NULL, should_bid TINYINT(1) NOT NULL, posted_at DATETIME DEFAULT NULL, client_name VARCHAR(255) DEFAULT NULL, client_review_count INT DEFAULT NULL, client_review_rating INT DEFAULT NULL, title LONGTEXT NOT NULL, description LONGTEXT NOT NULL, platform VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, raw_html LONGTEXT DEFAULT NULL, has_been_read TINYINT(1) DEFAULT NULL, has_been_read_at DATETIME DEFAULT NULL, budget VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE scrape (id INT AUTO_INCREMENT NOT NULL, hostname VARCHAR(255) NOT NULL, url LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, status_code INT DEFAULT NULL, body LONGTEXT NOT NULL, crawled_at DATETIME NOT NULL, scrape_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE scrape');
    }
}
