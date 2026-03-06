<?php

namespace App\Services;

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

        try {
            $mailer = $this->createMailer();
            $mailer->addAddress($to);
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
        $mailer->isHTML(false);

        return $mailer;
    }
}
