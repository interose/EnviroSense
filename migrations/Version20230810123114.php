<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230810123114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gas_daily (ts DATE NOT NULL, value INT DEFAULT NULL, total INT DEFAULT NULL, PRIMARY KEY(ts)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gas_hourly (ts DATETIME NOT NULL, value INT NOT NULL, PRIMARY KEY(ts)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE power_daily (ts DATE NOT NULL, value INT DEFAULT NULL, scaler INT DEFAULT NULL, total INT DEFAULT NULL, PRIMARY KEY(ts)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE power_hourly (ts DATETIME NOT NULL, value INT NOT NULL, scaler INT NOT NULL, PRIMARY KEY(ts)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sensor (id INT AUTO_INCREMENT NOT NULL, ts DATETIME NOT NULL, mac VARCHAR(255) NOT NULL, payload JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sensor_description (id INT AUTO_INCREMENT NOT NULL, mac VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, color VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE solar_daily (ts DATE NOT NULL, value INT DEFAULT NULL, total INT DEFAULT NULL, PRIMARY KEY(ts)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE solar_hourly (ts DATETIME NOT NULL, value INT NOT NULL, PRIMARY KEY(ts)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE gas_daily');
        $this->addSql('DROP TABLE gas_hourly');
        $this->addSql('DROP TABLE power_daily');
        $this->addSql('DROP TABLE power_hourly');
        $this->addSql('DROP TABLE sensor');
        $this->addSql('DROP TABLE sensor_description');
        $this->addSql('DROP TABLE solar_daily');
        $this->addSql('DROP TABLE solar_hourly');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
