<?php

declare(strict_types=1);

namespace App\Services;

use FPDF;

class InvoicePdfGenerator
{
    private HistoryBookingPricingService $historyPricing;

    public function __construct(?HistoryBookingPricingService $historyPricing = null)
    {
        $this->historyPricing = $historyPricing ?? new HistoryBookingPricingService();
    }

    public function generateInvoicePdf(
        string $invoiceNumber,
        string $orderNumber,
        array $customer,
        array $lineItems
    ): string {
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetTitle('Haarlem Festival Invoice');
        $pdf->SetAuthor('Haarlem Festival');
        $pdf->SetAutoPageBreak(true, 18);
        $pdf->AddPage();

        $customerName = trim((string)($customer['first_name'] ?? '') . ' ' . (string)($customer['last_name'] ?? ''));
        $customerEmail = trim((string)($customer['email'] ?? ''));
        $issuedAt = $this->formatDate((string)($customer['created_at'] ?? ''));

        $this->renderHeader($pdf, $invoiceNumber, $orderNumber, $issuedAt);
        $this->renderCustomer($pdf, $customerName, $customerEmail);

        $pdf->Ln(4);
        $this->renderTableHeader($pdf);

        $grandTotal = 0.0;

        foreach ($lineItems as $lineItem) {
            $pricing = $this->resolvePricing($lineItem);
            $grandTotal += $pricing['total_price'];
            $this->renderLineItem($pdf, $lineItem, $pricing['unit_price'], $pricing['total_price'], $pricing['note']);
        }

        $this->renderTotals($pdf, $grandTotal);

        $targetPath = sys_get_temp_dir() . '/haarlem-festival-invoice-' . uniqid('', true) . '.pdf';
        $pdf->Output('F', $targetPath);

        return $targetPath;
    }

    private function renderHeader(FPDF $pdf, string $invoiceNumber, string $orderNumber, string $issuedAt): void
    {
        $pdf->SetFont('Arial', 'B', 22);
        $pdf->Cell(110, 10, $this->encodeText('Haarlem Festival'), 0, 0);
        $pdf->Cell(80, 10, $this->encodeText('Invoice'), 0, 1, 'R');

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(110, 6, $this->encodeText('Digital ticket purchase confirmation'), 0, 0);
        $pdf->Cell(80, 6, $this->encodeText('Invoice no.: ' . $invoiceNumber), 0, 1, 'R');
        $pdf->Cell(110, 6, $this->encodeText('All amounts are listed in EUR.'), 0, 0);
        $pdf->Cell(80, 6, $this->encodeText('Order no.: ' . $orderNumber), 0, 1, 'R');
        $pdf->Cell(110, 6, '', 0, 0);
        $pdf->Cell(80, 6, $this->encodeText('Issued on: ' . $issuedAt), 0, 1, 'R');
        $pdf->Ln(6);
    }

    private function renderCustomer(FPDF $pdf, string $customerName, string $customerEmail): void
    {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(95, 7, $this->encodeText('Bill to'), 0, 0);
        $pdf->Cell(95, 7, $this->encodeText('Supplier'), 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(95, 6, $this->encodeText($customerName !== '' ? $customerName : 'Festival guest'), 0, 0);
        $pdf->Cell(95, 6, $this->encodeText('Haarlem Festival'), 0, 1);
        $pdf->Cell(95, 6, $this->encodeText($customerEmail !== '' ? $customerEmail : '-'), 0, 0);
        $pdf->Cell(95, 6, $this->encodeText('Digital order fulfilment'), 0, 1);
        $pdf->Ln(4);
    }

    private function renderTableHeader(FPDF $pdf): void
    {
        $pdf->SetFillColor(245, 247, 250);
        $pdf->SetDrawColor(214, 222, 232);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(95, 8, $this->encodeText('Description'), 1, 0, 'L', true);
        $pdf->Cell(20, 8, $this->encodeText('Qty'), 1, 0, 'C', true);
        $pdf->Cell(35, 8, $this->encodeText('Unit price'), 1, 0, 'R', true);
        $pdf->Cell(40, 8, $this->encodeText('Line total'), 1, 1, 'R', true);
    }

    private function renderLineItem(FPDF $pdf, array $lineItem, float $unitPrice, float $lineTotal, string $note): void
    {
        if ($pdf->GetY() > 245) {
            $pdf->AddPage();
            $this->renderTableHeader($pdf);
        }

        $description = trim((string)($lineItem['title'] ?? 'Festival ticket'));
        $eventType = ucfirst(trim((string)($lineItem['event_type'] ?? 'event')));
        $details = [];

        $eventStartTime = trim((string)($lineItem['event_start_time'] ?? ''));
        if ($eventStartTime !== '') {
            $details[] = 'Starts: ' . $this->formatDate($eventStartTime);
        }

        $venueName = trim((string)($lineItem['venue_name'] ?? ''));
        if ($venueName !== '') {
            $details[] = 'Venue: ' . $venueName;
        }

        $passDate = $this->formatPassDate((string)($lineItem['pass_date'] ?? ''));
        if ($passDate !== '') {
            $details[] = 'Pass date: ' . $passDate;
        }

        if ($note !== '') {
            $details[] = $note;
        }

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(95, 8, $this->encodeText($this->truncate($description . ' (' . $eventType . ')', 52)), 1, 0);
        $pdf->Cell(20, 8, $this->encodeText((string)max(1, (int)($lineItem['quantity'] ?? 1))), 1, 0, 'C');
        $pdf->Cell(35, 8, $this->encodeText('EUR ' . number_format($unitPrice, 2, '.', '')), 1, 0, 'R');
        $pdf->Cell(40, 8, $this->encodeText('EUR ' . number_format($lineTotal, 2, '.', '')), 1, 1, 'R');

        if ($details !== []) {
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(92, 99, 112);
            $pdf->Cell(95, 6, $this->encodeText(implode(' | ', $details)), 'LRB', 0);
            $pdf->Cell(20, 6, '', 'RB', 0);
            $pdf->Cell(35, 6, '', 'RB', 0);
            $pdf->Cell(40, 6, '', 'RB', 1);
            $pdf->SetTextColor(0, 0, 0);
        }
    }

    private function renderTotals(FPDF $pdf, float $grandTotal): void
    {
        $pdf->Ln(6);
        $pdf->SetX(115);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(35, 8, $this->encodeText('Total'), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(40, 8, $this->encodeText('EUR ' . number_format($grandTotal, 2, '.', '')), 0, 1, 'R');

        $pdf->Ln(8);
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(
            0,
            5,
            $this->encodeText('This invoice confirms a completed digital ticket purchase. Your ticket PDF and QR codes are delivered electronically.')
        );
    }

    private function resolvePricing(array $lineItem): array
    {
        $quantity = max(1, (int)($lineItem['quantity'] ?? 1));
        $eventType = strtolower(trim((string)($lineItem['event_type'] ?? '')));
        $unitPrice = (float)($lineItem['unit_price'] ?? 0.0);

        if ($eventType === 'history') {
            $pricing = $this->historyPricing->resolvePricing(
                $unitPrice,
                isset($lineItem['family_price']) ? (float)$lineItem['family_price'] : null,
                $quantity
            );

            return [
                'unit_price' => $pricing['unit_price'],
                'total_price' => $pricing['total_price'],
                'note' => $pricing['applied_family_price'] ? 'Family bundle price applied' : '',
            ];
        }

        return [
            'unit_price' => $unitPrice,
            'total_price' => round($unitPrice * $quantity, 2),
            'note' => '',
        ];
    }

    private function formatDate(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return date('d M Y');
        }

        try {
            return (new \DateTimeImmutable($value))->format('d M Y H:i');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function formatPassDate(string $value): string
    {
        $value = trim($value);
        if ($value === '' || $value === '1000-01-01') {
            return '';
        }

        try {
            return (new \DateTimeImmutable($value))->format('d M Y');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function truncate(string $value, int $maxLength): string
    {
        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $maxLength - 3)) . '...';
    }

    private function encodeText(string $value): string
    {
        $encoded = iconv('UTF-8', 'windows-1252//TRANSLIT', $value);

        return $encoded !== false ? $encoded : $value;
    }
}