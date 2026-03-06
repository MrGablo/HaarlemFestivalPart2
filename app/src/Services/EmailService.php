<?php

namespace App\Services;

class EmailService
{
    private string $fromAddress;
    private string $fromName;

    public function __construct()
    {
        $this->fromAddress = trim((string)(getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@haarlemfestival.local'));
        $this->fromName = trim((string)(getenv('MAIL_FROM_NAME') ?: 'Haarlem Festival'));
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

    private function sendPlainText(string $to, string $subject, string $message): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $this->formatFromHeader(),
            'Reply-To: ' . $this->fromAddress,
        ];

        return @mail($to, $subject, $message, implode("\r\n", $headers));
    }

    private function sendWithAttachments(string $to, string $subject, string $message, array $attachments): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $boundary = 'bnd_' . bin2hex(random_bytes(12));

        $headers = [
            'MIME-Version: 1.0',
            'From: ' . $this->formatFromHeader(),
            'Reply-To: ' . $this->fromAddress,
            'Content-Type: multipart/mixed; boundary="' . $boundary . '"',
        ];

        $bodyParts = [];
        $bodyParts[] = '--' . $boundary;
        $bodyParts[] = 'Content-Type: text/plain; charset="UTF-8"';
        $bodyParts[] = 'Content-Transfer-Encoding: 7bit';
        $bodyParts[] = '';
        $bodyParts[] = $message;
        $bodyParts[] = '';

        foreach ($attachments as $attachment) {
            $path = (string)($attachment['path'] ?? '');
            $name = (string)($attachment['name'] ?? basename($path));

            if ($path === '' || !is_file($path) || !is_readable($path)) {
                continue;
            }

            $content = file_get_contents($path);
            if ($content === false) {
                continue;
            }

            $encoded = chunk_split(base64_encode($content));

            $bodyParts[] = '--' . $boundary;
            $bodyParts[] = 'Content-Type: application/pdf; name="' . addslashes($name) . '"';
            $bodyParts[] = 'Content-Transfer-Encoding: base64';
            $bodyParts[] = 'Content-Disposition: attachment; filename="' . addslashes($name) . '"';
            $bodyParts[] = '';
            $bodyParts[] = $encoded;
            $bodyParts[] = '';
        }

        $bodyParts[] = '--' . $boundary . '--';

        return @mail($to, $subject, implode("\r\n", $bodyParts), implode("\r\n", $headers));
    }

    private function formatFromHeader(): string
    {
        if ($this->fromName === '') {
            return $this->fromAddress;
        }

        return sprintf('"%s" <%s>', addslashes($this->fromName), $this->fromAddress);
    }
}
