<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddHistoryEventFamilyPrice extends AbstractMigration
{
    public function up(): void
    {
        $column = $this->fetchRow(
            "SELECT COLUMN_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'HistoryEvent'
               AND COLUMN_NAME = 'family_price'"
        );

        if (!is_array($column)) {
            $this->execute(
                "ALTER TABLE `HistoryEvent`
                 ADD COLUMN `family_price` DECIMAL(10,2) NOT NULL DEFAULT 60.00
                 AFTER `price`"
            );
        }

        $this->execute(
            "UPDATE `HistoryEvent`
             SET `family_price` = 60.00
             WHERE `family_price` <= 0"
        );
    }

    public function down(): void
    {
        $column = $this->fetchRow(
            "SELECT COLUMN_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'HistoryEvent'
               AND COLUMN_NAME = 'family_price'"
        );

        if (is_array($column)) {
            $this->execute('ALTER TABLE `HistoryEvent` DROP COLUMN `family_price`');
        }
    }
}