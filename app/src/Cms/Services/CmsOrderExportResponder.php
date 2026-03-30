<?php

declare(strict_types=1);

namespace App\Cms\Services;

use App\Cms\Models\OrderExportResult;

final class CmsOrderExportResponder
{
    public function stream(OrderExportResult $result): void
    {
        if ($result->format === 'excel') {
            $this->streamExcel($result->rows, $result->columns, $result->columnMap);
            return;
        }

        $this->streamCsv($result->rows, $result->columns, $result->columnMap);
    }

    /** @param array<int, array<string, string>> $rows */
    /** @param array<int, string> $columns */
    /** @param array<string, string> $columnMap */
    private function streamCsv(array $rows, array $columns, array $columnMap): void
    {
        $filename = 'orders_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'wb');
        if ($output === false) {
            http_response_code(500);
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
    private function streamExcel(array $rows, array $columns, array $columnMap): void
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

        $writer = new \XMLWriter();
        if ($writer->openUri('php://output') === false) {
            http_response_code(500);
            exit;
        }
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('Workbook');
        $writer->writeAttribute('xmlns', 'urn:schemas-microsoft-com:office:spreadsheet');
        $writer->writeAttribute('xmlns:ss', 'urn:schemas-microsoft-com:office:spreadsheet');
        $writer->startElement('Worksheet');
        $writer->writeAttribute('ss:Name', 'Orders');
        $writer->startElement('Table');

        $writer->startElement('Row');
        foreach ($headerLabels as $label) {
            $writer->startElement('Cell');
            $writer->startElement('Data');
            $writer->writeAttribute('ss:Type', 'String');
            $writer->text($label);
            $writer->endElement();
            $writer->endElement();
        }
        $writer->endElement();
        $writer->flush();

        foreach ($rows as $row) {
            $writer->startElement('Row');
            foreach ($headerLabels as $label) {
                $value = (string)($row[$label] ?? '');
                $isNumeric = in_array($label, $numericLabels, true) && is_numeric($value);
                $type = $isNumeric ? 'Number' : 'String';

                $writer->startElement('Cell');
                $writer->startElement('Data');
                $writer->writeAttribute('ss:Type', $type);
                $writer->text($value);
                $writer->endElement();
                $writer->endElement();
            }
            $writer->endElement();
            $writer->flush();
        }

        $writer->endElement();
        $writer->endElement();
        $writer->endElement();
        $writer->endDocument();
        $writer->flush();
        exit;
    }
}
