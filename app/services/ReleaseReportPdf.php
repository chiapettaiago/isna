<?php
declare(strict_types=1);

class ReleaseReportPdf
{
    public static function render(array $report): string
    {
        return self::buildPdf(self::reportLines($report));
    }

    private static function reportLines(array $report): array
    {
        $lines = [
            (string)($report['title'] ?? 'Relatorio'),
            (string)($report['label'] ?? 'Relatorio'),
            'Lancamento: ' . (string)($report['period'] ?? ''),
            'Status: ' . (string)($report['status'] ?? ''),
            'Gerado em: ' . (new DateTimeImmutable())->format('d/m/Y H:i'),
            '',
            (string)($report['description'] ?? ''),
            '',
            'Resumo',
        ];

        foreach (($report['summaryCards'] ?? []) as $card) {
            if (!is_array($card)) {
                continue;
            }
            $lines[] = (string)($card['label'] ?? '') . ': ' . (string)($card['value'] ?? '');
            $lines[] = (string)($card['text'] ?? '');
        }

        $lines[] = '';
        $lines[] = 'Mudancas realizadas';

        foreach (($report['changeSections'] ?? []) as $section) {
            if (!is_array($section)) {
                continue;
            }

            $lines[] = '';
            $lines[] = (string)($section['title'] ?? '');
            $lines[] = (string)($section['intro'] ?? '');

            foreach (($section['items'] ?? []) as $item) {
                $lines[] = '- ' . (string)$item;
            }

            $lines[] = 'Resultado: ' . (string)($section['result'] ?? '');
        }

        $lines[] = '';
        $lines[] = 'Antes e depois';

        foreach (($report['deliveryRows'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $lines[] = (string)($row['area'] ?? '');
            $lines[] = 'Antes: ' . (string)($row['before'] ?? '');
            $lines[] = 'Depois da 2.1: ' . (string)($row['after'] ?? '');
        }

        $lines[] = '';
        $lines[] = 'Criterios de qualidade';

        foreach (($report['qualityChecks'] ?? []) as $check) {
            $lines[] = '- ' . (string)$check;
        }

        return $lines;
    }

    private static function buildPdf(array $lines): string
    {
        $pages = array_chunk(self::wrapLines($lines), 48);
        if (empty($pages)) {
            $pages = [[]];
        }

        $objects = [];
        $pageObjectNumbers = [];
        $fontObjectNumber = 3 + count($pages) * 2;

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        $pageKids = [];
        foreach ($pages as $pageIndex => $pageLines) {
            $pageObjectNumber = 3 + ($pageIndex * 2);
            $contentObjectNumber = $pageObjectNumber + 1;
            $pageObjectNumbers[] = $pageObjectNumber;
            $pageKids[] = $pageObjectNumber . ' 0 R';
            $objects[$pageObjectNumber] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 ' . $fontObjectNumber . ' 0 R >> >> /Contents ' . $contentObjectNumber . ' 0 R >>';
            $content = self::pageContent($pageLines, $pageIndex + 1, count($pages));
            $objects[$contentObjectNumber] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $pageKids) . '] /Count ' . count($pageObjectNumbers) . ' >>';
        $objects[$fontObjectNumber] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }

    private static function wrapLines(array $lines): array
    {
        $wrapped = [];

        foreach ($lines as $line) {
            $line = self::normalizeText((string)$line);
            if ($line === '') {
                $wrapped[] = '';
                continue;
            }

            while (strlen($line) > 96) {
                $breakAt = strrpos(substr($line, 0, 96), ' ');
                if ($breakAt === false || $breakAt < 40) {
                    $breakAt = 96;
                }
                $wrapped[] = substr($line, 0, $breakAt);
                $line = ltrim(substr($line, $breakAt));
            }

            $wrapped[] = $line;
        }

        return $wrapped;
    }

    private static function pageContent(array $lines, int $page, int $totalPages): string
    {
        $content = "BT\n/F1 10 Tf\n14 TL\n50 790 Td\n";
        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $content .= "T*\n";
            }
            $content .= '(' . self::escapePdfText($line) . ") Tj\n";
        }

        $content .= "ET\n";
        $content .= "BT\n/F1 9 Tf\n50 36 Td\n(Pagina " . $page . " de " . $totalPages . ") Tj\nET";

        return $content;
    }

    private static function normalizeText(string $text): string
    {
        $converted = function_exists('iconv') ? @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text) : false;
        if ($converted !== false) {
            return $converted;
        }

        return preg_replace('/[^\x20-\x7E]/', '', $text) ?? '';
    }

    private static function escapePdfText(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
