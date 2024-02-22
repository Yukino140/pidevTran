<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240221121611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, id_transaction_id INT NOT NULL, tax DOUBLE PRECISION NOT NULL, montant_ttc DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_FE86641012A67609 (id_transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE86641012A67609 FOREIGN KEY (id_transaction_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D172F0DA07 FOREIGN KEY (id_compte_id) REFERENCES compte (id)');
        $this->addSql('CREATE INDEX IDX_723705D172F0DA07 ON transaction (id_compte_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE86641012A67609');
        $this->addSql('DROP TABLE facture');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D172F0DA07');
        $this->addSql('DROP INDEX IDX_723705D172F0DA07 ON transaction');
    }
}
