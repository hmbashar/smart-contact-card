<?php
namespace Smartcc\Services;

class VCardService
{
    public function build(array $data): string
    {
        $e = fn($s) => str_replace(["\r", "\n", ",", ";"], ["", "", "\\,", "\\;"], trim((string)$s));
        $name = $data['name'] ?? 'Contact';

        $lines = [
            'BEGIN:VCARD', 'VERSION:3.0',
            'N:;' . $e($name) . ';;;',
            'FN:' . $e($name),
        ];
        if (!empty($data['org']))     $lines[] = 'ORG:' . $e($data['org']);
        if (!empty($data['title']))   $lines[] = 'TITLE:' . $e($data['title']);
        if (!empty($data['phone']))   $lines[] = 'TEL;TYPE=CELL,VOICE:' . $e($data['phone']);
        if (!empty($data['email']))   $lines[] = 'EMAIL;TYPE=INTERNET:' . $e($data['email']);
        if (!empty($data['website'])) $lines[] = 'URL:' . $e($data['website']);
        if (!empty($data['address'])) $lines[] = 'ADR;TYPE=WORK:;;' . $e($data['address']) . ';;;';
        $lines[] = 'END:VCARD';
        return implode("\r\n", $lines);
    }

    public function dataUrl(string $vcard): string
    {
        return 'data:text/vcard;charset=utf-8,' . rawurlencode($vcard);
    }
}
