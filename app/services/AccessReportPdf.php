<?php
declare(strict_types=1);

class AccessReportPdf
{
    public static function render(array $report, DateTimeImmutable $fromDate, DateTimeImmutable $toDate): string
    {
        $lines = self::reportLines($report, $fromDate, $toDate);
        return self::buildPdf($lines);
    }

    private static function reportLines(array $report, DateTimeImmutable $fromDate, DateTimeImmutable $toDate): array
    {
        $totals = isset($report['totals']) && is_array($report['totals']) ? $report['totals'] : [];
        $daily = isset($report['daily']) && is_array($report['daily']) ? $report['daily'] : [];
        $topPaths = isset($report['top_paths']) && is_array($report['top_paths']) ? $report['top_paths'] : [];
        $recent = isset($report['recent']) && is_array($report['recent']) ? $report['recent'] : [];

        $lines = [
            'Relatorio de acessos - ISNA',
            'Periodo: ' . $fromDate->format('d/m/Y') . ' a ' . $toDate->format('d/m/Y'),
            'Gerado em: ' . (new DateTimeImmutable())->format('d/m/Y H:i'),
            '',
            'Resumo',
            'Total de acessos: ' . number_format((int)($totals['accesses'] ?? 0), 0, ',', '.'),
            'IPs unicos: ' . number_format((int)($totals['unique_ips'] ?? 0), 0, ',', '.'),
            'Paginas acessadas: ' . number_format((int)($totals['unique_paths'] ?? 0), 0, ',', '.'),
            '',
            'Acessos por dia',
        ];

        if (empty($daily)) {
            $lines[] = 'Sem acessos no periodo.';
        } else {
            foreach ($daily as $day => $count) {
                $date = DateTimeImmutable::createFromFormat('Y-m-d', (string)$day);
                $lines[] = ($date ? $date->format('d/m/Y') : (string)$day) . ': ' . (int)$count;
            }
        }

        $lines[] = '';
        $lines[] = 'Paginas mais acessadas';

        if (empty($topPaths)) {
            $lines[] = 'Sem paginas no periodo.';
        } else {
            foreach ($topPaths as $index => $row) {
                $lines[] = ($index + 1) . '. ' . (string)($row['path'] ?? '') . ' - ' . (int)($row['accesses'] ?? 0) . ' acessos';
            }
        }

        $lines[] = '';
        $lines[] = 'Acessos recentes';

        if (empty($recent)) {
            $lines[] = 'Sem acessos recentes no periodo.';
        } else {
            foreach (array_slice($recent, 0, 30) as $row) {
                $ts = isset($row['ts']) ? (int)$row['ts'] : 0;
                $date = $ts > 0 ? date('d/m/Y H:i', $ts) : '--';
                $ip = isset($row['ip']) && $row['ip'] !== '' ? (string)$row['ip'] : 'IP nao informado';
                $lines[] = $date . ' | ' . $ip . ' | ' . (string)($row['path'] ?? '');
            }
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
        $objects[$fontObjectNumber] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
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
            $content .= '(' . self::escapePdfText($line) . ') Tj\n';
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
