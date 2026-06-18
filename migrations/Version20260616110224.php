<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260616110224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE trajet_preference (trajet_id INT NOT NULL, preference_id INT NOT NULL, INDEX IDX_428F67C3D12A823 (trajet_id), INDEX IDX_428F67C3D81022C0 (preference_id), PRIMARY KEY (trajet_id, preference_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE trajet_preference ADD CONSTRAINT FK_428F67C3D12A823 FOREIGN KEY (trajet_id) REFERENCES Trajet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE trajet_preference ADD CONSTRAINT FK_428F67C3D81022C0 FOREIGN KEY (preference_id) REFERENCES Preference (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Avis ADD reservation_id INT NOT NULL');
        $this->addSql('ALTER TABLE Avis ADD CONSTRAINT FK_2FA304CEB83297E7 FOREIGN KEY (reservation_id) REFERENCES Reservation (id)');
        $this->addSql('CREATE INDEX IDX_2FA304CEB83297E7 ON Avis (reservation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trajet_preference DROP FOREIGN KEY FK_428F67C3D12A823');
        $this->addSql('ALTER TABLE trajet_preference DROP FOREIGN KEY FK_428F67C3D81022C0');
        $this->addSql('DROP TABLE trajet_preference');
        $this->addSql('ALTER TABLE Avis DROP FOREIGN KEY FK_2FA304CEB83297E7');
        $this->addSql('DROP INDEX IDX_2FA304CEB83297E7 ON Avis');
        $this->addSql('ALTER TABLE Avis DROP reservation_id');
    }
}
