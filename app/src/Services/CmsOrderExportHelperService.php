<?php

declare(strict_types=1);

namespace App\Services;

final class CmsOrderExportHelperService
{
    /** @param mixed $requestedColumns */
    /** @param array<string, string> $columnMap */
    /** @param array<int, string> $defaultColumns */
    /** @return array<int, string> */
    public function normalizeColumnsFromMap(mixed $requestedColumns, array $columnMap, array $defaultColumns): array
    {
        if (!is_array($requestedColumns) || $requestedColumns === []) {
            return $defaultColumns;
        }

        $allowed = array_keys($columnMap);
        $selected = [];
        foreach ($requestedColumns as $column) {
            $key = trim((string)$column);
            if ($key !== '' && in_array($key, $allowed, true) && !in_array($key, $selected, true)) {
                $selected[] = $key;
            }
        }

        return $selected !== [] ? $selected : $defaultColumns;
    }

    /** @param array<int, array<string, string>> $exportRows */
    /** @param array<int, string> $columns */
    /** @param array<string, string> $columnMap */
    /** @return array<int, array<string, string>> */
    public function buildSafeRows(array $exportRows, array $columns, array $columnMap): array
    {
        $safeRows = [];
        foreach ($exportRows as $exportRow) {
            $row = [];
            foreach ($columns as $column) {
                if (!array_key_exists($column, $columnMap)) {
                    continue;
                }

                $row[$columnMap[$column]] = (string)($exportRow[$column] ?? '');
            }
            $safeRows[] = $row;
        }

        return $safeRows;
    }

    /** @param array<int, string> $columns */
    /** @param array<string, string> $columnMap */
    /** @param array{payed: float, pending: float, general: float} $statusTotals */
    /** @return array<int, array<string, string>> */
    public function buildStatusSummaryRows(array $columns, array $columnMap, array $statusTotals): array
    {
        $labels = [];
        foreach ($columns as $column) {
            if (isset($columnMap[$column])) {
                $labels[$column] = $columnMap[$column];
            }
        }

        $labelColumnPreference = ['customer_name', 'event_title', 'order_id'];
        $labelColumn = null;
        foreach ($labelColumnPreference as $candidate) {
            if (isset($labels[$candidate])) {
                $labelColumn = $candidate;
                break;
            }
        }
        if ($labelColumn === null && $columns !== []) {
            $first = (string)$columns[0];
            if (isset($labels[$first])) {
                $labelColumn = $first;
            }
        }

        $totalColumnPreference = ['order_total', 'line_total', 'unit_price'];
        $totalColumn = null;
        foreach ($totalColumnPreference as $candidate) {
            if (isset($labels[$candidate])) {
                $totalColumn = $candidate;
                break;
            }
        }

        $buildSingleRow = function (string $label, float $value) use ($labels, $labelColumn, $totalColumn): array {
            $row = [];
            foreach ($labels as $displayLabel) {
                $row[$displayLabel] = '';
            }

            if ($labelColumn !== null) {
                $row[$labels[$labelColumn]] = $label;
            }

            if ($totalColumn !== null) {
                $row[$labels[$totalColumn]] = number_format($value, 2, '.', '');
            }

            return $row;
        };

        return [
            $buildSingleRow('TOTAL PAYED', (float)$statusTotals['payed']),
            $buildSingleRow('TOTAL PENDING', (float)$statusTotals['pending']),
            $buildSingleRow('TOTAL GENERAL', (float)$statusTotals['general']),
        ];
    }
}
