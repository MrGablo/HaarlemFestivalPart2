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
        return self::renderSvg($data, true);
    }

    public static function generateSvgMarkup(string $data): string
    {
        return self::renderSvg($data, false);
    }

    private static function renderSvg(string $data, bool $outputBase64): string
    {
        if (empty(trim($data))) {
            throw new InvalidArgumentException('QR code data cannot be empty.');
        }

        $qrcode = new QRCode([
            'version'         => -1,
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel'        => 'L',
            'scale'           => 5,
            'outputBase64'    => $outputBase64,
            'svgAddXmlHeader' => false,
        ]);

        return $qrcode->render($data);
    }
}
