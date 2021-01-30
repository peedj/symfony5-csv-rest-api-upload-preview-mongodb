<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210129125510 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, file_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(150) NOT NULL, file_path LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file_data (id INT AUTO_INCREMENT NOT NULL, file_id INT NOT NULL, date DATE NOT NULL, client VARCHAR(255) NOT NULL, sign_smartid INT NOT NULL, sign_mobile INT NOT NULL, sign_sc INT NOT NULL, authorize_smartid INT NOT NULL, authorize_mobile INT NOT NULL, authorize_sc INT NOT NULL, ocsp INT NOT NULL, crl INT NOT NULL, INDEX IDX_240F9B0D93CB796C (file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE file_data ADD CONSTRAINT FK_240F9B0D93CB796C FOREIGN KEY (file_id) REFERENCES file (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file_data DROP FOREIGN KEY FK_240F9B0D93CB796C');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE file_data');
    }
}
