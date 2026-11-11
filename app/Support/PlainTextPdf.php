<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Minimal PDF 1.4 writer for receipt text. Helvetica/WinAnsi only;
 * non-ASCII is replaced. No extra PDF package.
 */
final class PlainTextPdf
{
    /**
     * @param  list<string>  $lines
     */
    public function render(array $lines): string
    {
        $commands = "BT\n/F1 12 Tf\n50 760 Td\n14 TL\n";

        foreach ($lines as $line) {
            $commands .= '('.$this->escape($line).") '\n";
        }

        $commands .= "ET\n";
        $length = strlen($commands);

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            4 => '<< /Length '.$length." >>\nstream\n".$commands.'endstream',
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number." 0 obj\n".$body."\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= 'xref\n0 6\n';
        $pdf .= "0000000000 65535 f \n";

        for ($number = 1; $number <= 5; $number++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$number]);
        }

        $pdf .= "trailer << /Size 6 /Root 1 0 R >>\n";
        $pdf .= "startxref\n".$xref."\n%%EOF\n";

        return $pdf;
    }

    private function escape(string $text): string
    {
        $ascii = preg_replace('/[^\x20-\x7E]/', '?', $text) ?? '?';

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $ascii);
    }
}
