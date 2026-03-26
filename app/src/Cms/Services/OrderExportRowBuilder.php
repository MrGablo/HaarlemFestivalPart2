<?php

declare(strict_types=1);

namespace App\Cms\Services;

final class OrderExportRowBuilder
{
    /**
     * @param array<int, array<string, mixed>> $orders
     * @param array<int, array<string, mixed>> $allItems
     * @return array<int, array<string, string>>
     */
    public function buildRows(array $orders, array $allItems): array
    {
        $orderIds = array_map(fn(array $o): int => (int)($o['order_id'] ?? 0), $orders);
        $orderIds = array_values(array_filter($orderIds, fn(int $id): bool => $id > 0));

        $itemsByOrder = [];
        foreach ($allItems as $itemRow) {
            $oid = (int)($itemRow['order_id'] ?? 0);
            $itemsByOrder[$oid][] = $itemRow;
        }

        $summaryByOrder = [];
        foreach ($orders as $orderSummary) {
            $oid = (int)($orderSummary['order_id'] ?? 0);
            $summaryByOrder[$oid] = $orderSummary;
        }

        $rows = [];
        foreach ($orderIds as $oid) {
            $orderSummary = $summaryByOrder[$oid] ?? null;
            if ($orderSummary === null) {
                continue;
            }

            $rawItems = $itemsByOrder[$oid] ?? [];

            $items = [];
            $calculatedTotal = 0.0;
            foreach ($rawItems as $row) {
                $quantity = max(0, (int)($row['quantity'] ?? 0));
                $unitPrice = (float)($row['price'] ?? 0.0);
                $lineTotal = $quantity * $unitPrice;
                $calculatedTotal += $lineTotal;

                $items[] = [
                    'order_item_id' => (int)($row['order_item_id'] ?? 0),
                    'event_id'      => (int)($row['event_id'] ?? 0),
                    'title'         => (string)($row['title'] ?? 'Event'),
                    'event_type'    => (string)($row['event_type'] ?? ''),
                    'artist_name'   => (string)($row['artist_name'] ?? ''),
                    'venue_name'    => (string)($row['venue_name'] ?? ''),
                    'start_date'    => (string)($row['start_date'] ?? ''),
                    'quantity'      => $quantity,
                    'unit_price'    => $unitPrice,
                    'line_total'    => $lineTotal,
                ];
            }

            $orderTotal = $calculatedTotal;

            if ($items === []) {
                $rows[] = [
                    'order_id' => (string)($orderSummary['order_id'] ?? ''),
                    'user_id' => (string)($orderSummary['user_id'] ?? ''),
                    'customer_name' => (string)($orderSummary['customer_name'] ?? ''),
                    'customer_email' => (string)($orderSummary['customer_email'] ?? ''),
                    'status' => (string)($orderSummary['status'] ?? ''),
                    'order_created_at' => (string)($orderSummary['created_at'] ?? ''),
                    'order_item_id' => '',
                    'event_id' => '',
                    'event_title' => '',
                    'event_type' => '',
                    'artist_name' => '',
                    'venue_name' => '',
                    'event_start_date' => '',
                    'quantity' => '0',
                    'unit_price' => number_format(0, 2, '.', ''),
                    'line_total' => number_format(0, 2, '.', ''),
                    'order_total' => number_format($orderTotal, 2, '.', ''),
                ];

                continue;
            }

            foreach ($items as $item) {
                $unitPrice = (float)($item['unit_price'] ?? 0.0);
                $lineTotal = (float)($item['line_total'] ?? 0.0);

                $rows[] = [
                    'order_id' => (string)($orderSummary['order_id'] ?? ''),
                    'user_id' => (string)($orderSummary['user_id'] ?? ''),
                    'customer_name' => (string)($orderSummary['customer_name'] ?? ''),
                    'customer_email' => (string)($orderSummary['customer_email'] ?? ''),
                    'status' => (string)($orderSummary['status'] ?? ''),
                    'order_created_at' => (string)($orderSummary['created_at'] ?? ''),
                    'order_item_id' => (string)($item['order_item_id'] ?? ''),
                    'event_id' => (string)($item['event_id'] ?? ''),
                    'event_title' => (string)($item['title'] ?? ''),
                    'event_type' => (string)($item['event_type'] ?? ''),
                    'artist_name' => (string)($item['artist_name'] ?? ''),
                    'venue_name' => (string)($item['venue_name'] ?? ''),
                    'event_start_date' => (string)($item['start_date'] ?? ''),
                    'quantity' => (string)($item['quantity'] ?? 0),
                    'unit_price' => number_format($unitPrice, 2, '.', ''),
                    'line_total' => number_format($lineTotal, 2, '.', ''),
                    'order_total' => number_format($orderTotal, 2, '.', ''),
                ];
            }
        }

        return $rows;
    }

    /** @param array<string, string> $allColumns */
    /** @param array<int, array<string, string>> $orderRows */
    /** @return array<string, string> */
    public function availableColumnsForRows(array $allColumns, array $orderRows): array
    {
        if ($orderRows === []) {
            return [];
        }

        $available = [];
        foreach ($allColumns as $key => $label) {
            foreach ($orderRows as $row) {
                $value = (string)($row[$key] ?? '');
                if (trim($value) !== '') {
                    $available[$key] = $label;
                    break;
                }
            }
        }

        return $available;
    }

    /**
     * @param mixed $requestedColumns
     * @param array<string, string> $columnMap
     * @param array<int, string> $defaultColumns
     * @return array<int, string>
     */
    public function normalizeColumns(mixed $requestedColumns, array $columnMap, array $defaultColumns): array
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

    /**
     * @param array<int, string> $columns
     * @param array<string, string> $columnMap
     * @param array{payed: float, pending: float, general: float} $statusTotals
     * @return array<int, array<string, string>>
     */
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
