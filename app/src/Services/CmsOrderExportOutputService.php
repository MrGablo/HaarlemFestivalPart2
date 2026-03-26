<?php

declare(strict_types=1);

namespace App\Services;

final class CmsOrderExportOutputService
{
    /** @param array<int, array<string, string>> $rows */
    /** @param array<int, string> $columns */
    /** @param array<string, string> $columnMap */
    public function outputCsv(array $rows, array $columns, array $columnMap): void
    {
        $filename = 'orders_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'wb');
        if ($output === false) {
            http_response_code(500);
            echo 'Could not generate export.';
            exit;
        }

        $headerLabels = array_map(fn(string $column): string => (string)($columnMap[$column] ?? $column), $columns);
        fputcsv($output, $headerLabels, ',', '"', '\\');

        foreach ($rows as $row) {
            $line = [];
            foreach ($headerLabels as $label) {
                $line[] = $row[$label] ?? '';
            }
            fputcsv($output, $line, ',', '"', '\\');
        }

        fclose($output);
        exit;
    }

    /** @param array<int, array<string, string>> $rows */
    /** @param array<int, string> $columns */
    /** @param array<string, string> $columnMap */
    public function outputExcel(array $rows, array $columns, array $columnMap): void
    {
        $filename = 'orders_export_' . date('Ymd_His') . '.xml';

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $headerLabels = array_map(fn(string $column): string => (string)($columnMap[$column] ?? $column), $columns);
        $numericLabels = [
            'Order ID',
            'User ID',
            'Order Item ID',
            'Event ID',
            'Quantity',
            'Price Per Item',
            'Line Total',
            'Order Total',
        ];

        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        echo "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"";
        echo " xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\">";
        echo "<Worksheet ss:Name=\"Orders\"><Table>";

        echo '<Row>';
        foreach ($headerLabels as $label) {
            echo '<Cell><Data ss:Type="String">' . $this->xmlEscape($label) . '</Data></Cell>';
        }
        echo '</Row>';

        foreach ($rows as $row) {
            echo '<Row>';
            foreach ($headerLabels as $label) {
                $value = (string)($row[$label] ?? '');
                $isNumeric = in_array($label, $numericLabels, true) && is_numeric($value);
                $type = $isNumeric ? 'Number' : 'String';
                echo '<Cell><Data ss:Type="' . $type . '">' . $this->xmlEscape($value) . '</Data></Cell>';
            }
            echo '</Row>';
        }

        echo '</Table></Worksheet></Workbook>';

        exit;
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
