<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240204110557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_product_ref (order_id INT NOT NULL, item_id INT NOT NULL, quantity INT DEFAULT 1 NOT NULL, INDEX IDX_EFF2153A8D9F6D38 (order_id), INDEX IDX_EFF2153A126F525E (item_id), PRIMARY KEY(order_id, item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_product_ref ADD CONSTRAINT FK_EFF2153A8D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_product_ref ADD CONSTRAINT FK_EFF2153A126F525E FOREIGN KEY (item_id) REFERENCES product_reference (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_product_ref DROP FOREIGN KEY FK_EFF2153A8D9F6D38');
        $this->addSql('ALTER TABLE order_product_ref DROP FOREIGN KEY FK_EFF2153A126F525E');
        $this->addSql('DROP TABLE order_product_ref');
    }
}
