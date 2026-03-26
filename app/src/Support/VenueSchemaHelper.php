<?php

declare(strict_types=1);

namespace App\Support;

use PDO;

/**
 * Resolves Venue table name, primary key, and a SQL expression for human-readable venue label.
 */
final class VenueSchemaHelper
{
    private static ?string $primaryKeyColumn = null;

    private static ?string $venueTableName = null;

    /** @var list<string>|null */
    private static ?array $venueColumnsLower = null;

    public static function venueTableName(PDO $pdo): string
    {
        if (self::$venueTableName !== null) {
            return self::$venueTableName;
        }

        $stmt = $pdo->query(
            "SELECT TABLE_NAME AS t FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND LOWER(TABLE_NAME) = 'venue' LIMIT 1"
        );
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
        self::$venueTableName = (is_array($row) && !empty($row['t'])) ? (string) $row['t'] : 'Venue';

        return self::$venueTableName;
    }

    public static function primaryKeyColumn(PDO $pdo): string
    {
        if (self::$primaryKeyColumn !== null) {
            return self::$primaryKeyColumn;
        }

        $t = str_replace(['`', "'"], '', self::venueTableName($pdo));
        $stmt = $pdo->query(
            "SELECT COLUMN_NAME AS c FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $pdo->quote($t) . "
               AND CONSTRAINT_NAME = 'PRIMARY'
             ORDER BY ORDINAL_POSITION LIMIT 1"
        );
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
        $c = is_array($row) ? (string) ($row['c'] ?? '') : '';
        if ($c !== '' && preg_match('/^[a-zA-Z0-9_]+$/', $c)) {
            self::$primaryKeyColumn = $c;
        } else {
            self::$primaryKeyColumn = 'venue_id';
        }

        return self::$primaryKeyColumn;
    }

    /**
     * SQL expression (using alias v) for venue display name, e.g. COALESCE(v.name, …).
     */
    public static function displayNameExpression(PDO $pdo, string $alias = 'v'): string
    {
        $t = str_replace(['`', "'"], '', self::venueTableName($pdo));
        if (self::$venueColumnsLower === null) {
            self::$venueColumnsLower = [];
            $stmt = $pdo->query(
                'SELECT COLUMN_NAME AS c FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ' . $pdo->quote($t)
            );
            while ($stmt && ($r = $stmt->fetch(PDO::FETCH_ASSOC))) {
                self::$venueColumnsLower[] = strtolower((string) ($r['c'] ?? ''));
            }
        }

        $vpk = self::primaryKeyColumn($pdo);
        $coalesce = [];
        foreach (['name', 'venue_name', 'title', 'location', 'address'] as $col) {
            if (in_array(strtolower($col), self::$venueColumnsLower ?? [], true)) {
                $coalesce[] = "NULLIF(TRIM({$alias}.`{$col}`),'')";
            }
        }
        if ($coalesce === []) {
            return "CONCAT('Venue ', {$alias}.`{$vpk}`)";
        }

        return 'COALESCE(' . implode(', ', $coalesce) . ", CONCAT('Venue ', {$alias}.`{$vpk}`))";
    }
}
