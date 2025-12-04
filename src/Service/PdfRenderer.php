<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Filesystem\Filesystem;

class PdfRenderer
{
    public function __construct(
        private readonly ?string $defaultFont = null,
        private readonly ?string $fontDirectory = null,
        private readonly ?string $tempDirectory = null,
        private readonly Filesystem $filesystem,
    ) {
    }

    /**
     * Render HTML into a PDF document using Dompdf.
     */
    public function render(string $html, array $options = []): string
    {
        $dompdfOptions = new Options();
        $dompdfOptions->set('isRemoteEnabled', true);
        $dompdfOptions->set('isHtml5ParserEnabled', true);
        $dompdfOptions->set('defaultFont', $options['default_font'] ?? $this->defaultFont ?? 'Helvetica');

        $fontDir = $this->prepareDirectory($this->fontDirectory);
        $tempDir = $this->prepareDirectory($this->tempDirectory);

        if ($fontDir) {
            $dompdfOptions->set('fontDir', $fontDir);
            $dompdfOptions->set('fontCache', $fontDir);
        }

        if ($tempDir) {
            $dompdfOptions->set('tempDir', $tempDir);
        }

        if (!empty($options['dpi'])) {
            $dompdfOptions->set('dpi', (int) $options['dpi']);
        }

        $dompdf = new Dompdf($dompdfOptions);

        $orientation = !empty($options['landscape'])
            ? 'landscape'
            : ($options['orientation'] ?? 'portrait');

        $dompdf->setPaper($options['format'] ?? 'A4', $orientation);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        return $dompdf->output();
    }

    private function prepareDirectory(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (!$this->filesystem->exists($path)) {
            $this->filesystem->mkdir($path, 0775);
        }

        return $path;
    }
}
