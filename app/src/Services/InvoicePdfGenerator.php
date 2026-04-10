<?php

declare(strict_types=1);

namespace App\Services;

use FPDF;

class InvoicePdfGenerator
{
    /** @param array<int, array<string, mixed>> $lineItems */
    public function generateInvoicePdf(string $orderNumber, string $customerName, array $lineItems, ?string $createdAt = null): string
    {
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetTitle('Haarlem Festival Invoice');
        $pdf->SetAuthor('Haarlem Festival');
        $pdf->SetAutoPageBreak(true, 18);
        $pdf->AddPage();

        $this->renderHeader($pdf, $orderNumber, $customerName, $createdAt);

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(90, 8, 'Description', 1, 0, 'L');
        $pdf->Cell(25, 8, 'Quantity', 1, 0, 'C');
        $pdf->Cell(35, 8, 'Unit Price', 1, 0, 'R');
        $pdf->Cell(40, 8, 'Line Total', 1, 1, 'R');

        $pdf->SetFont('Arial', '', 10);
        $grandTotal = 0.0;

        foreach ($lineItems as $item) {
            $title = trim((string)($item['title'] ?? 'Festival booking'));
            $quantity = max(1, (int)($item['quantity'] ?? 1));
            $unitPrice = (float)($item['price'] ?? 0.0);
            $lineTotal = $quantity * $unitPrice;
            $grandTotal += $lineTotal;

            $pdf->Cell(90, 8, $this->truncate($title, 44), 1, 0, 'L');
            $pdf->Cell(25, 8, (string)$quantity, 1, 0, 'C');
            $pdf->Cell(35, 8, 'EUR ' . number_format($unitPrice, 2, '.', ''), 1, 0, 'R');
            $pdf->Cell(40, 8, 'EUR ' . number_format($lineTotal, 2, '.', ''), 1, 1, 'R');
        }

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(150, 10, 'Total', 1, 0, 'R');
        $pdf->Cell(40, 10, 'EUR ' . number_format($grandTotal, 2, '.', ''), 1, 1, 'R');

        $pdf->Ln(8);
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(0, 6, 'This invoice confirms payment for your Haarlem Festival booking. Keep it for your records.');

        $targetPath = sys_get_temp_dir() . '/haarlem-festival-invoice-' . uniqid('', true) . '.pdf';
        $pdf->Output('F', $targetPath);

        return $targetPath;
    }

    private function renderHeader(FPDF $pdf, string $orderNumber, string $customerName, ?string $createdAt): void
    {
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, 'Haarlem Festival Invoice', 0, 1);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 7, 'Order number: ' . $orderNumber, 0, 1);
        $pdf->Cell(0, 7, 'Customer: ' . ($customerName !== '' ? $customerName : 'Festival guest'), 0, 1);
        $pdf->Cell(0, 7, 'Invoice date: ' . $this->formatDate($createdAt), 0, 1);
        $pdf->Ln(6);
    }

    private function formatDate(?string $createdAt): string
    {
        $value = $createdAt !== null ? trim($createdAt) : '';
        if ($value === '') {
            return date('Y-m-d H:i');
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $value;
        }

        return date('Y-m-d H:i', $timestamp);
    }

    private function truncate(string $value, int $maxLength): string
    {
        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, max(1, $maxLength - 3))) . '...';
    }
}