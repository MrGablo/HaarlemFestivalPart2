<?php

declare(strict_types=1);

namespace App\Utils;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Output\QRMarkupSVG;
use InvalidArgumentException;

class QrGenerator
{
    /**
     * Generates a base64 encoded SVG image of a QR code based on the input string.
     * 
     * @param string $data The data to encode in the QR code.
     * @return string The base64 data URI string that can be used directly in an <img> tag's src attribute.
     */
    public static function generate(string $data): string
    {
        if (empty(trim($data))) {
            throw new InvalidArgumentException('QR code data cannot be empty.');
        }

        $qrcode = new QRCode([
            'version'         => -1,               // -1 is the v6 equivalent for auto-detecting the version size
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel'        => 'L',              // v6 allows simple strings for ECC level
            'scale'           => 5,
            'outputBase64'    => true,             // Ensures we get a data URI string back instead of raw bytes
        ]);

        return $qrcode->render($data);
    }
}
