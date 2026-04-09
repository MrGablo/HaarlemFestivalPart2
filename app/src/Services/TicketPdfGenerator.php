<?php

declare(strict_types=1);

namespace App\Services;

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

        $this->renderHeader($pdf, $orderNumber, $customerName, count($tickets));

        foreach ($tickets as $index => $ticket) {
            $this->renderTicketBlock($pdf, $ticket, $index + 1);
        }

        $targetPath = sys_get_temp_dir() . '/haarlem-festival-tickets-' . uniqid('', true) . '.pdf';
        $pdf->Output('F', $targetPath);

        return $targetPath;
    }

    private function renderHeader(FPDF $pdf, string $orderNumber, string $customerName, int $ticketCount): void
    {
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, 'Haarlem Festival Tickets', 0, 1);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 7, 'Order number: ' . $orderNumber, 0, 1);
        $pdf->Cell(0, 7, 'Ticket holder: ' . ($customerName !== '' ? $customerName : 'Festival guest'), 0, 1);
        $pdf->Cell(0, 7, 'Tickets included: ' . $ticketCount, 0, 1);
        $pdf->Ln(4);

        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(
            0,
            6,
            'Present the QR codes included in the confirmation email at the venue entrance. The ticket codes listed below match those QR images.'
        );
        $pdf->Ln(4);
    }

    private function renderTicketBlock(FPDF $pdf, array $ticket, int $sequence): void
    {
        if ($pdf->GetY() > 235) {
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
        $height = 44;

        $pdf->SetDrawColor(214, 222, 232);
        $pdf->Rect($x, $y, $width, $height);

        $pdf->SetXY($x + 4, $y + 4);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(0, 7, sprintf('%02d. %s', $sequence, $title), 0, 1);

        $pdf->SetX($x + 4);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, 'Ticket #' . $ticketId . ' | ' . $eventType, 0, 1);

        if ($startsAt !== '') {
            $pdf->SetX($x + 4);
            $pdf->Cell(0, 6, 'Starts: ' . $startsAt, 0, 1);
        }

        if ($venue !== '') {
            $pdf->SetX($x + 4);
            $pdf->Cell(0, 6, 'Venue: ' . $venue, 0, 1);
        }

        $pdf->SetX($x + 4);
        $pdf->Cell(0, 6, 'Price: EUR ' . number_format($price, 2, '.', ''), 0, 1);

        $pdf->SetX($x + 4);
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(0, 5, 'Ticket code: ' . $ticketCode);
        $pdf->Ln(4);
    }
}