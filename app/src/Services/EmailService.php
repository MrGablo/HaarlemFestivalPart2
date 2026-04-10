<?php

namespace App\Services;

use App\Utils\QrGenerator;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class EmailService
{
    private string $fromAddress;
    private string $fromName;
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUser;
    private string $smtpPass;
    private string $smtpEncryption;

    public function __construct()
    {
        $this->fromAddress = trim((string)(getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@haarlemfestival.local'));
        $this->fromName = trim((string)(getenv('MAIL_FROM_NAME') ?: 'Haarlem Festival'));

        $this->smtpHost = trim((string)(getenv('SMTP_HOST') ?: ''));
        $this->smtpPort = (int)(getenv('SMTP_PORT') ?: 587);
        $this->smtpUser = trim((string)(getenv('SMTP_USERNAME') ?: ''));
        $this->smtpPass = (string)(getenv('SMTP_PASSWORD') ?: '');
        $this->smtpEncryption = strtolower(trim((string)(getenv('SMTP_ENCRYPTION') ?: 'tls')));
    }

    public function sendRegistrationConfirmation(string $email, string $firstName): bool
    {
        $subject = 'Welcome to Haarlem Festival';
        $body = "Hi {$firstName},\n\n"
            . "Your account was created successfully.\n"
            . "You can now log in and manage your bookings.\n\n"
            . "Kind regards,\n"
            . "Haarlem Festival";

        return $this->sendPlainText($email, $subject, $body);
    }

    public function sendPasswordResetConfirmation(string $email, string $firstName): bool
    {
        $subject = 'Your password was changed';
        $body = "Hi {$firstName},\n\n"
            . "This is a confirmation that your password was changed successfully.\n"
            . "If this was not you, please contact support immediately.\n\n"
            . "Kind regards,\n"
            . "Haarlem Festival";

        return $this->sendPlainText($email, $subject, $body);
    }

    public function sendPasswordResetLink(string $email, string $firstName, string $resetUrl): bool
    {
        $subject = 'Reset your Haarlem Festival password';
        $body = "Hi {$firstName},\n\n"
            . "We received a request to reset your password.\n"
            . "Use the link below to choose a new password:\n\n"
            . "{$resetUrl}\n\n"
            . "This link expires in 1 hour.\n"
            . "If you did not request this, you can ignore this email.\n\n"
            . "Kind regards,\n"
            . "Haarlem Festival";

        return $this->sendPlainText($email, $subject, $body);
    }

    public function sendAccountUpdateConfirmation(string $email, string $firstName): bool
    {
        $subject = 'Account updated';
        $body = "Hi {$firstName},\n\n"
            . "Your account details were updated successfully.\n\n"
            . "Kind regards,\n"
            . "Haarlem Festival";

        return $this->sendPlainText($email, $subject, $body);
    }

    public function sendTicketsAndInvoice(
        string $email,
        string $firstName,
        string $orderNumber,
        string $ticketPdfPath,
        string $invoicePdfPath
    ): bool {
        $subject = "Your Haarlem Festival tickets and invoice (Order {$orderNumber})";
        $body = "Hi {$firstName},\n\n"
            . "Thank you for your payment.\n"
            . "Your tickets and invoice are attached as PDF files.\n\n"
            . "Order number: {$orderNumber}\n\n"
            . "Enjoy the festival!\n"
            . "Haarlem Festival";

        $attachments = [
            ['path' => $ticketPdfPath, 'name' => 'tickets.pdf'],
            ['path' => $invoicePdfPath, 'name' => 'invoice.pdf'],
        ];

        return $this->sendWithAttachments($email, $subject, $body, $attachments);
    }

    public function sendTicketDelivery(
        string $email,
        string $firstName,
        string $orderNumber,
        string $ticketPdfPath,
        string $invoicePdfPath,
        array $tickets
    ): bool {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            $mailer = $this->createMailer();
            $mailer->addAddress($email);
            $mailer->isHTML(true);
            $mailer->Subject = "Your Haarlem Festival tickets (Order {$orderNumber})";

            $htmlTickets = [];
            $textTickets = [];

            foreach ($tickets as $index => $ticket) {
                $ticketId = (int)($ticket['ticket_id'] ?? 0);
                $title = htmlspecialchars((string)($ticket['title'] ?? 'Festival Ticket'), ENT_QUOTES, 'UTF-8');
                $startsAt = trim((string)($ticket['event_start_time'] ?? ''));
                $venue = trim((string)($ticket['venue_name'] ?? ''));
                $qr = trim((string)($ticket['qr'] ?? ''));
                $cid = 'ticket-qr-' . ($ticketId > 0 ? (string)$ticketId : (string)$index);
                $hasQrImage = false;

                if ($qr !== '') {
                    try {
                        $mailer->addStringEmbeddedImage(
                            QrGenerator::generatePngData($qr),
                            $cid,
                            'ticket-' . ($ticketId > 0 ? (string)$ticketId : (string)$index) . '.png',
                            PHPMailer::ENCODING_BASE64,
                            'image/png'
                        );
                        $hasQrImage = true;
                    } catch (\Throwable $e) {
                        error_log('Ticket QR embed failed: ' . $e->getMessage());
                    }
                }

                $details = [];
                if ($startsAt !== '') {
                    $details[] = htmlspecialchars($startsAt, ENT_QUOTES, 'UTF-8');
                }
                if ($venue !== '') {
                    $details[] = htmlspecialchars($venue, ENT_QUOTES, 'UTF-8');
                }

                $htmlTickets[] = '<div style="margin:0 0 24px;padding:20px;border:1px solid #d6dee8;border-radius:12px;background:#f9fbfd;">'
                    . '<h2 style="margin:0 0 8px;font-size:18px;line-height:1.4;color:#111827;">' . $title . '</h2>'
                    . '<p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#4b5563;">Ticket #' . $ticketId . '</p>'
                    . (!empty($details)
                        ? '<p style="margin:0 0 12px;font-size:14px;line-height:1.5;color:#4b5563;">' . implode(' | ', $details) . '</p>'
                        : '')
                    . ($hasQrImage
                        ? '<img src="cid:' . htmlspecialchars($cid, ENT_QUOTES, 'UTF-8') . '" alt="QR code for ticket ' . $ticketId . '" style="display:block;width:220px;height:220px;background:#ffffff;border:1px solid #d6dee8;border-radius:8px;padding:8px;">'
                        : '')
                    . '<p style="margin:12px 0 0;font-size:13px;line-height:1.5;color:#6b7280;">Ticket code: ' . htmlspecialchars($qr, ENT_QUOTES, 'UTF-8') . '</p>'
                    . '</div>';

                $textTicket = "- {$title} (Ticket #{$ticketId})";
                if ($startsAt !== '') {
                    $textTicket .= "\n  Starts: {$startsAt}";
                }
                if ($venue !== '') {
                    $textTicket .= "\n  Venue: {$venue}";
                }
                if ($qr !== '') {
                    $textTicket .= "\n  Ticket code: {$qr}";
                }
                $textTickets[] = $textTicket;
            }

            $mailer->Body = '<div style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#111827;">'
                . '<p style="margin:0 0 16px;">Hi ' . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . ',</p>'
                . '<p style="margin:0 0 16px;">Thank you for your payment. Your ticket PDF and invoice PDF are attached, and each QR code is included in this e-mail for quick entry at the venue.</p>'
                . '<p style="margin:0 0 20px;"><strong>Order number:</strong> ' . htmlspecialchars($orderNumber, ENT_QUOTES, 'UTF-8') . '</p>'
                . implode('', $htmlTickets)
                . '<p style="margin:24px 0 0;">Enjoy the festival!<br>Haarlem Festival</p>'
                . '</div>';
            $mailer->AltBody = "Hi {$firstName},\n\n"
                . "Thank you for your payment. Your ticket PDF and invoice PDF are attached to this email.\n"
                . "Order number: {$orderNumber}\n\n"
                . implode("\n\n", $textTickets)
                . "\n\nEnjoy the festival!\nHaarlem Festival";

            if ($ticketPdfPath !== '' && is_file($ticketPdfPath) && is_readable($ticketPdfPath)) {
                $mailer->addAttachment($ticketPdfPath, 'tickets.pdf');
            }

            if ($invoicePdfPath !== '' && is_file($invoicePdfPath) && is_readable($invoicePdfPath)) {
                $mailer->addAttachment($invoicePdfPath, 'invoice.pdf');
            }

            $mailer->send();
            return true;
        } catch (\Throwable $e) {
            error_log('Ticket delivery email failed: ' . $e->getMessage());
            return false;
        }
    }

    private function sendPlainText(string $to, string $subject, string $message): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            $mailer = $this->createMailer();
            $mailer->addAddress($to);
            $mailer->isHTML(false);
            $mailer->Subject = $subject;
            $mailer->Body = $message;
            $mailer->send();
            return true;
        } catch (\Throwable $e) {
            error_log('Email send failed: ' . $e->getMessage());
            return false;
        }
    }

    private function sendWithAttachments(string $to, string $subject, string $message, array $attachments): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            $mailer = $this->createMailer();
            $mailer->addAddress($to);
            $mailer->isHTML(false);
            $mailer->Subject = $subject;
            $mailer->Body = $message;

            foreach ($attachments as $attachment) {
                $path = (string)($attachment['path'] ?? '');
                $name = (string)($attachment['name'] ?? basename($path));
                if ($path === '' || !is_file($path) || !is_readable($path)) {
                    continue;
                }
                $mailer->addAttachment($path, $name);
            }

            $mailer->send();
            return true;
        } catch (\Throwable $e) {
            error_log('Email send with attachment failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @throws \RuntimeException
     * @throws Exception
     */
    private function createMailer(): PHPMailer
    {
        if ($this->smtpHost === '' || $this->smtpUser === '' || $this->smtpPass === '') {
            throw new \RuntimeException('SMTP is not configured.');
        }

        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $this->smtpHost;
        $mailer->Port = $this->smtpPort;
        $mailer->SMTPAuth = true;
        $mailer->Username = $this->smtpUser;
        $mailer->Password = $this->smtpPass;
        $mailer->CharSet = 'UTF-8';

        if ($this->smtpEncryption === 'ssl') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mailer->setFrom($this->fromAddress, $this->fromName);

        return $mailer;
    }
}
