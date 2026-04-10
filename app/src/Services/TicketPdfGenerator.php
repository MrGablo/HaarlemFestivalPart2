<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\QrGenerator;
use FPDF;

class TicketPdfGenerator
{
    public function generateTicketsPdf(string $orderNumber, string $customerName, array $tickets): string
    {
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetTitle('Haarlem Festival Tickets');
        $pdf->SetAuthor('Haarlem Festival');
        $pdf->SetAutoPageBreak(true, 18);
        $pdf->AddPage();

        $tempFiles = [];

        try {
            $this->renderHeader($pdf, $orderNumber, $customerName, count($tickets));

            foreach ($tickets as $index => $ticket) {
                $qrImagePath = $this->createTemporaryQrImage((string)($ticket['qr'] ?? ''));
                if ($qrImagePath !== null) {
                    $tempFiles[] = $qrImagePath;
                }

                $this->renderTicketBlock($pdf, $ticket, $index + 1, $qrImagePath);
            }

            $targetPath = sys_get_temp_dir() . '/haarlem-festival-tickets-' . uniqid('', true) . '.pdf';
            $pdf->Output('F', $targetPath);

            return $targetPath;
        } finally {
            foreach ($tempFiles as $tempFile) {
                if (is_file($tempFile)) {
                    @unlink($tempFile);
                }
            }
        }
    }

    private function renderHeader(FPDF $pdf, string $orderNumber, string $customerName, int $ticketCount): void
    {
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, $this->encodeText('Haarlem Festival Tickets'), 0, 1);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 7, $this->encodeText('Order number: ' . $orderNumber), 0, 1);
        $pdf->Cell(0, 7, $this->encodeText('Ticket holder: ' . ($customerName !== '' ? $customerName : 'Festival guest')), 0, 1);
        $pdf->Cell(0, 7, $this->encodeText('Tickets included: ' . $ticketCount), 0, 1);
        $pdf->Ln(4);

        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(
            0,
            6,
            $this->encodeText('Each ticket includes its entry QR code. Present the attached PDF or the confirmation email at the venue entrance.')
        );
        $pdf->Ln(4);
    }

    private function renderTicketBlock(FPDF $pdf, array $ticket, int $sequence, ?string $qrImagePath): void
    {
        if ($pdf->GetY() > 228) {
            $pdf->AddPage();
        }

        $title = (string)($ticket['title'] ?? 'Festival Ticket');
        $eventType = ucfirst((string)($ticket['event_type'] ?? 'event'));
        $startsAt = trim((string)($ticket['event_start_time'] ?? ''));
        $venue = trim((string)($ticket['venue_name'] ?? ''));
        $ticketCode = trim((string)($ticket['qr'] ?? ''));
        $ticketId = (int)($ticket['ticket_id'] ?? 0);
        $price = (float)($ticket['price'] ?? 0);

        $x = 10;
        $y = $pdf->GetY();
        $width = 190;
        $height = 58;
        $contentWidth = $qrImagePath !== null ? 132 : 178;

        $pdf->SetDrawColor(214, 222, 232);
        $pdf->Rect($x, $y, $width, $height);

        $pdf->SetXY($x + 4, $y + 4);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell($contentWidth, 7, $this->encodeText(sprintf('%02d. %s', $sequence, $title)), 0, 1);

        $pdf->SetX($x + 4);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($contentWidth, 6, $this->encodeText('Ticket #' . $ticketId . ' | ' . $eventType), 0, 1);

        if ($startsAt !== '') {
            $pdf->SetX($x + 4);
            $pdf->Cell($contentWidth, 6, $this->encodeText('Starts: ' . $startsAt), 0, 1);
        }

        if ($venue !== '') {
            $pdf->SetX($x + 4);
            $pdf->Cell($contentWidth, 6, $this->encodeText('Venue: ' . $venue), 0, 1);
        }

        $pdf->SetX($x + 4);
        $pdf->Cell($contentWidth, 6, $this->encodeText('Price: EUR ' . number_format($price, 2, '.', '')), 0, 1);

        $pdf->SetX($x + 4);
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell($contentWidth, 5, $this->encodeText('Ticket code: ' . $ticketCode));

        if ($qrImagePath !== null && is_file($qrImagePath)) {
            $pdf->Image($qrImagePath, $x + 145, $y + 8, 34, 34, 'PNG');
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetXY($x + 142, $y + 44);
            $pdf->Cell(40, 5, $this->encodeText('Scan at entry'), 0, 0, 'C');
        }

        $pdf->SetY($y + $height);
        $pdf->Ln(4);
    }

    private function createTemporaryQrImage(string $qr): ?string
    {
        $qr = trim($qr);
        if ($qr === '') {
            return null;
        }

        $targetPath = sys_get_temp_dir() . '/haarlem-festival-ticket-qr-' . uniqid('', true) . '.png';
        file_put_contents($targetPath, QrGenerator::generatePngData($qr));

        return $targetPath;
    }

    private function encodeText(string $value): string
    {
        $encoded = iconv('UTF-8', 'windows-1252//TRANSLIT', $value);

        return $encoded !== false ? $encoded : $value;
    }
}