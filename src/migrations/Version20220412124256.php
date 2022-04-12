<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220412124256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
    }
    public function postUp(Schema $schema): void
    {
        $data = [
            ['name' => 'Василий', 'middlename' => 'Семёнович', 'surname' => 'Гроссман'],
            ['name' => 'Василий1', 'middlename' => 'Семёнович', 'surname' => 'Гроссман'],
            ['name' => 'Василий2', 'middlename' => 'Семёнович', 'surname' => 'Гроссман'],
            ['name' => 'Василий3', 'middlename' => 'Семёнович', 'surname' => 'Гроссман'],
            ['name' => 'Василий4', 'middlename' => 'Семёнович', 'surname' => 'Гроссман']
        ];
        foreach ($data as $item) {
            $this->connection->insert('author', $item);
        }
    }
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
