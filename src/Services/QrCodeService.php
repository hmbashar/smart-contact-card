<?php
namespace Smartcc\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeService
{
    public function svg(string $payload, int $size = 180): string
    {
        $qr = QrCode::create($payload)->withSize($size)->withMargin(2);
        return (new SvgWriter())->write($qr)->getString();
    }
}
