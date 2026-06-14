<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260614113524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Avis (id INT AUTO_INCREMENT NOT NULL, note INT NOT NULL, commentaire LONGTEXT NOT NULL, approuve TINYINT DEFAULT 0 NOT NULL, conducteur_id INT NOT NULL, passager_id INT NOT NULL, INDEX IDX_2FA304CEF16F4AC6 (conducteur_id), INDEX IDX_2FA304CE71A51189 (passager_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE Preference (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_1234B3836C6E55B5 (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE Reservation (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(20) NOT NULL, dateReservation DATETIME NOT NULL, trajet_id INT NOT NULL, passager_id INT NOT NULL, INDEX IDX_C454C682D12A823 (trajet_id), INDEX IDX_C454C68271A51189 (passager_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE Role (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_F75B25546C6E55B5 (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE Trajet (id INT AUTO_INCREMENT NOT NULL, villeDepart VARCHAR(100) NOT NULL, villeArrivee VARCHAR(100) NOT NULL, dateDepart DATETIME NOT NULL, dateArrivee DATETIME NOT NULL, prix DOUBLE PRECISION NOT NULL, placesDispo INT NOT NULL, eco TINYINT NOT NULL, statut VARCHAR(20) NOT NULL, conducteur_id INT NOT NULL, vehicule_id INT NOT NULL, INDEX IDX_2CF7ACBAF16F4AC6 (conducteur_id), INDEX IDX_2CF7ACBA4A4A3511 (vehicule_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE User (id INT AUTO_INCREMENT NOT NULL, pseudo VARCHAR(50) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, isActive TINYINT NOT NULL, photo VARCHAR(255) NOT NULL, credits INT NOT NULL, createdAt DATETIME NOT NULL, UNIQUE INDEX UNIQ_2DA17977E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE Vehicule (id INT AUTO_INCREMENT NOT NULL, marque VARCHAR(100) NOT NULL, modele VARCHAR(100) NOT NULL, couleur VARCHAR(50) NOT NULL, energie VARCHAR(50) NOT NULL, places INT NOT NULL, user_id INT NOT NULL, INDEX IDX_D0599D4BA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vehicule_preferences (vehicule_id INT NOT NULL, preference_id INT NOT NULL, INDEX IDX_AF0407164A4A3511 (vehicule_id), INDEX IDX_AF040716D81022C0 (preference_id), PRIMARY KEY (vehicule_id, preference_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE Avis ADD CONSTRAINT FK_2FA304CEF16F4AC6 FOREIGN KEY (conducteur_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE Avis ADD CONSTRAINT FK_2FA304CE71A51189 FOREIGN KEY (passager_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE Reservation ADD CONSTRAINT FK_C454C682D12A823 FOREIGN KEY (trajet_id) REFERENCES Trajet (id)');
        $this->addSql('ALTER TABLE Reservation ADD CONSTRAINT FK_C454C68271A51189 FOREIGN KEY (passager_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE Trajet ADD CONSTRAINT FK_2CF7ACBAF16F4AC6 FOREIGN KEY (conducteur_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE Trajet ADD CONSTRAINT FK_2CF7ACBA4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES Vehicule (id)');
        $this->addSql('ALTER TABLE Vehicule ADD CONSTRAINT FK_D0599D4BA76ED395 FOREIGN KEY (user_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE vehicule_preferences ADD CONSTRAINT FK_AF0407164A4A3511 FOREIGN KEY (vehicule_id) REFERENCES Vehicule (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vehicule_preferences ADD CONSTRAINT FK_AF040716D81022C0 FOREIGN KEY (preference_id) REFERENCES Preference (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Avis DROP FOREIGN KEY FK_2FA304CEF16F4AC6');
        $this->addSql('ALTER TABLE Avis DROP FOREIGN KEY FK_2FA304CE71A51189');
        $this->addSql('ALTER TABLE Reservation DROP FOREIGN KEY FK_C454C682D12A823');
        $this->addSql('ALTER TABLE Reservation DROP FOREIGN KEY FK_C454C68271A51189');
        $this->addSql('ALTER TABLE Trajet DROP FOREIGN KEY FK_2CF7ACBAF16F4AC6');
        $this->addSql('ALTER TABLE Trajet DROP FOREIGN KEY FK_2CF7ACBA4A4A3511');
        $this->addSql('ALTER TABLE Vehicule DROP FOREIGN KEY FK_D0599D4BA76ED395');
        $this->addSql('ALTER TABLE vehicule_preferences DROP FOREIGN KEY FK_AF0407164A4A3511');
        $this->addSql('ALTER TABLE vehicule_preferences DROP FOREIGN KEY FK_AF040716D81022C0');
        $this->addSql('DROP TABLE Avis');
        $this->addSql('DROP TABLE Preference');
        $this->addSql('DROP TABLE Reservation');
        $this->addSql('DROP TABLE Role');
        $this->addSql('DROP TABLE Trajet');
        $this->addSql('DROP TABLE User');
        $this->addSql('DROP TABLE Vehicule');
        $this->addSql('DROP TABLE vehicule_preferences');
    }
}
