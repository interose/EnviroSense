<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250129092324 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE anker_daily (date DATE NOT NULL, battery_discharge INT NOT NULL, home_usage INT NOT NULL, grid_to_home INT NOT NULL, solar_production INT NOT NULL, battery_charge INT NOT NULL, solar_to_grid INT NOT NULL, battery_percentage INT NOT NULL, solar_percentage INT NOT NULL, PRIMARY KEY(date)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE anker_hourly (ts DATETIME NOT NULL, power_unit VARCHAR(10) NOT NULL, solar_power1 INT NOT NULL, solar_power2 INT NOT NULL, solar_power3 INT NOT NULL, solar_power4 INT NOT NULL, battery_soc INT NOT NULL, battery_energy INT NOT NULL, charging_power INT NOT NULL, PRIMARY KEY(ts)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE anker_daily');
        $this->addSql('DROP TABLE anker_hourly');
    }
}
