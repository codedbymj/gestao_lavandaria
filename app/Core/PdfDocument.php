<?php

declare(strict_types=1);

namespace App\Core;

final class PdfDocument
{
    private array $pages = [];
    private int $currentPage = -1;

    public function addPage(): void
    {
        $this->pages[] = [];
        $this->currentPage = count($this->pages) - 1;
    }

    public function text(float $x, float $top, string $text, float $size = 10, bool $bold = false): void
    {
        if ($this->currentPage < 0) $this->addPage();
        $font = $bold ? 'F2' : 'F1';
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT', $text) ?: $text;
        $encoded = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded);
        $y = 842 - $top;
        $this->pages[$this->currentPage][] = sprintf(
            "BT /%s %.2F Tf %.2F %.2F Td (%s) Tj ET",
            $font,
            $size,
            $x,
            $y,
            $encoded
        );
    }

    public function line(float $x1, float $top1, float $x2, float $top2): void
    {
        if ($this->currentPage < 0) $this->addPage();
        $this->pages[$this->currentPage][] = sprintf(
            "%.2F %.2F m %.2F %.2F l S",
            $x1,
            842 - $top1,
            $x2,
            842 - $top2
        );
    }

    public function output(string $filename): never
    {
        $objects = [];
        $pageCount = count($this->pages);
        $kids = [];
        for ($i = 0; $i < $pageCount; $i++) $kids[] = (6 + $i * 2) . ' 0 R';

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $pageCount . ' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $objects[5] = '<< >>';

        foreach ($this->pages as $index => $commands) {
            $pageId = 6 + $index * 2;
            $contentId = $pageId + 1;
            $content = implode("\n", $commands);
            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents {$contentId} 0 R >>";
            $objects[$contentId] = "<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream";
        }

        ksort($objects);
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $maxId = max(array_keys($objects));
        $pdf .= "xref\n0 " . ($maxId + 1) . "\n0000000000 65535 f \n";
        for ($id = 1; $id <= $maxId; $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        }
        $pdf .= "trailer\n<< /Size " . ($maxId + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename) . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }
}
