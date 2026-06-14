<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;
use Symfony\Component\Process\Process;

class PrescriptionPdfService
{
    private const MAX_ANALYSIS_SECONDS = 10;
    private const MIN_PROCESS_SECONDS = 1;

    private ?float $analysisDeadlineAt = null;

    /**
     * Devuelve una receta en formato:
     * - od: ['sph' => ?float, 'cyl' => ?float, 'axis' => ?int, 'add' => ?float]
     * - oi: ['sph' => ?float, 'cyl' => ?float, 'axis' => ?int, 'add' => ?float]
     * - dp: ['binocular' => ?float, 'od' => ?float, 'oi' => ?float]
     * - warnings: string[]
     */
    public function extractPrescriptionFromUpload(UploadedFile $archivo): array
    {
        $this->analysisDeadlineAt = microtime(true) + self::MAX_ANALYSIS_SECONDS;

        // OCR/parseo puede tardar; dejamos un margen pequeño pero controlado.
        try {
            @set_time_limit(self::MAX_ANALYSIS_SECONDS + 2);
            @ini_set('max_execution_time', (string) (self::MAX_ANALYSIS_SECONDS + 2));
        } catch (\Throwable) {
            // noop
        }

        if (!$archivo->isValid()) {
            throw new \RuntimeException('El archivo subido no es válido.');
        }

        // En Windows, getRealPath() puede devolver vacío de forma intermitente.
        // getPathname() es más confiable para archivos temporales subidos.
        $pathname = $archivo->getPathname();
        if (!is_string($pathname) || trim($pathname) === '') {
            throw new \RuntimeException('No pude acceder al archivo subido (ruta temporal vacía). Intenta seleccionarlo nuevamente.');
        }

        if (!is_file($pathname)) {
            $realPath = $archivo->getRealPath();
            if (!is_string($realPath) || trim($realPath) === '' || !is_file($realPath)) {
                throw new \RuntimeException('No pude acceder al archivo subido (archivo temporal no encontrado). Intenta seleccionarlo nuevamente.');
            }
        }

        $warnings = [];
        $meta = [
            'engine' => null,
            'ocr_used' => false,
            'mime' => $archivo->getMimeType(),
            'client_mime' => method_exists($archivo, 'getClientMimeType') ? $archivo->getClientMimeType() : null,
            'original_name' => $archivo->getClientOriginalName(),
            'pdf_text_extract_failed' => false,
            'pdf_magic_ok' => null,
        ];

        // En Windows, UploadedFile->store() puede fallar porque internamente usa getRealPath() y devuelve false.
        // Para evitarlo, guardamos el archivo leyendo desde getPathname() (stream).
        $storedPath = null;
        $absolutePath = null;

        $text = '';
        try {
            $disk = Storage::disk('local');
            $dir = 'tmp/prescriptions';
            $disk->makeDirectory($dir);

            $originalName = (string) $archivo->getClientOriginalName();
            $ext = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
            if ($ext === '') {
                $mimeLower = strtolower((string) ($archivo->getMimeType() ?? ''));
                $ext = str_contains($mimeLower, 'pdf') ? 'pdf' : 'bin';
            }

            $storedPath = $dir . '/' . Str::uuid() . '.' . $ext;

            $stream = @fopen($pathname, 'rb');
            if ($stream === false) {
                throw new \RuntimeException('No pude leer el archivo subido desde su ruta temporal.');
            }

            try {
                $ok = $disk->put($storedPath, $stream);
            } finally {
                try {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                } catch (\Throwable) {
                    // noop
                }
            }

            if ($ok !== true) {
                throw new \RuntimeException('No se pudo guardar el archivo temporal.');
            }

            $absolutePath = $disk->path($storedPath);
            if (!is_string($absolutePath) || trim($absolutePath) === '' || !is_file($absolutePath)) {
                throw new \RuntimeException('No se encontró el archivo temporal para procesarlo.');
            }

            // Verificación real (no solo extensión/mime): si parece PDF, valida el encabezado "%PDF-".
            $nameLower = strtolower($originalName);
            $mime = strtolower((string) ($archivo->getMimeType() ?? ''));
            $looksPdf = str_contains($mime, 'pdf') || Str::endsWith($nameLower, '.pdf');
            if ($looksPdf) {
                try {
                    $fh = @fopen($absolutePath, 'rb');
                    if ($fh !== false) {
                        $head = (string) fread($fh, 5);
                        fclose($fh);
                        $meta['pdf_magic_ok'] = ($head === '%PDF-');
                        if ($meta['pdf_magic_ok'] === false) {
                            $warnings[] = 'El archivo parece PDF por nombre/mime, pero no tiene encabezado %PDF-. Puede estar corrupto o no ser un PDF real.';
                        }
                    }
                } catch (\Throwable) {
                    // no-op
                }
            }

            if ($looksPdf) {
                try {
                    $text = $this->readTextFromPdf($absolutePath);
                    $meta['engine'] = 'smalot/pdfparser';
                } catch (\Throwable $e) {
                    // Algunos PDFs (firmados, protegidos, con fuentes raras, etc.) pueden romper el parser.
                    // No queremos fallar duro: intentamos OCR si hay herramientas.
                    report($e);
                    $meta['pdf_text_extract_failed'] = true;
                    $warnings[] = 'No pude extraer texto directo del PDF. Intentando OCR…';
                    $text = '';
                }

                $text = $this->normalizeText($text);

                // Si el PDF viene escaneado (sin texto), intenta OCR.
                if (mb_strlen(trim($text)) < 40) {
                    if (!$this->hasTimeBudget()) {
                        $warnings[] = 'Se agotó el tiempo máximo de análisis antes de intentar OCR.';
                    } else {
                    $warnings[] = 'El PDF parece ser un escaneo (imagen) o no trae texto útil. Intentando OCR…';

                    $ocr = $this->ocrTextFromPdf($absolutePath, $warnings);
                    if ($ocr !== null) {
                        $meta['engine'] = 'tesseract';
                        $meta['ocr_used'] = true;
                        $text = $this->normalizeText($ocr);
                    }
                    }
                }
            } else {
                // Imagen directa: OCR.
                $ocr = $this->hasTimeBudget() ? $this->ocrTextFromImage($absolutePath, $warnings) : null;
                if ($ocr !== null) {
                    $meta['engine'] = 'tesseract';
                    $meta['ocr_used'] = true;
                    $text = $this->normalizeText($ocr);
                } else {
                    $alreadySaidTesseract = false;
                    foreach ($warnings as $w) {
                        if (is_string($w) && stripos($w, 'tesseract') !== false) {
                            $alreadySaidTesseract = true;
                            break;
                        }
                    }

                    if (!$alreadySaidTesseract) {
                        $warnings[] = 'No se pudo ejecutar OCR sobre la imagen.';
                    }
                }
            }
        } finally {
            try {
                // No dejamos que el borrado del temporal rompa el flujo.
                if (is_string($storedPath) && trim($storedPath) !== '') {
                    Storage::disk('local')->delete($storedPath);
                }
            } catch (\Throwable) {
                // noop
            }
        }

        if (mb_strlen(trim($text)) < 10) {
            $warnings[] = 'No pude obtener texto suficiente del archivo.';
        }

        $od = $this->extractEye($text, ['OD', 'DER', 'DERECHO', 'RIGHT']);
        $oi = $this->extractEye($text, ['OI', 'OS', 'IZQ', 'IZQUIERDO', 'LEFT']);
        $dp = $this->extractDp($text);

        // Intentar tabla LEJOS/CERCA siempre como "candidato" (en estas recetas la extracción por tokens suele mezclar filas).
        $byTable = $this->extractLejosTable($text);
        $od = $this->pickBestEye($od, $byTable['od'] ?? []);
        $oi = $this->pickBestEye($oi, $byTable['oi'] ?? []);

        // Si no se encontró nada, intenta extracción por tablas/columnas.
        if ($this->isEmptyEye($od) && $this->isEmptyEye($oi)) {
            $byColumns = $this->extractByColumns($text);
            $od = $this->mergeEye($od, $byColumns['od'] ?? []);
            $oi = $this->mergeEye($oi, $byColumns['oi'] ?? []);
        }

        if ($this->isEmptyEye($od) && $this->isEmptyEye($oi) && $dp['binocular'] === null && $dp['od'] === null && $dp['oi'] === null) {
            $warnings[] = 'No logré detectar una fórmula clara en el PDF. Si quieres, pégame aquí un ejemplo de texto del PDF y ajusto los patrones.';
        }

        if (function_exists('app')) {
            try {
                if (app()->isLocal()) {
                    $meta['text_preview'] = mb_substr($text, 0, 500);
                }
            } catch (\Throwable) {
                // noop
            }
        }

        return [
            'od' => $od,
            'oi' => $oi,
            'dp' => $dp,
            'warnings' => $warnings,
            'meta' => $meta,
        ];
    }

    public function extractPrescriptionFromPdf(UploadedFile $pdf): array
    {
        // Compatibilidad con el método anterior.
        return $this->extractPrescriptionFromUpload($pdf);
    }

    private function readTextFromPdf(string $absolutePath): string
    {
        $parser = new Parser();
        $document = $parser->parseFile($absolutePath);
        return (string) $document->getText();
    }

    private function ocrTextFromPdf(string $absolutePdfPath, array &$warnings): ?string
    {
        if (!$this->hasTimeBudget()) {
            $warnings[] = 'OCR omitido: se agotó el tiempo máximo de análisis.';
            return null;
        }

        $imagePath = $this->pdfToPngFirstPage($absolutePdfPath, $warnings);
        if ($imagePath === null) {
            return null;
        }

        try {
            return $this->ocrTextFromImage($imagePath, $warnings);
        } finally {
            try {
                if (is_string($imagePath) && trim($imagePath) !== '' && is_file($imagePath)) {
                    @unlink($imagePath);
                }
            } catch (\Throwable) {
                // noop
            }
        }
    }

    private function pdfToPngFirstPage(string $absolutePdfPath, array &$warnings): ?string
    {
        if (!$this->hasTimeBudget()) {
            $warnings[] = 'No alcanzó el tiempo para convertir el PDF a imagen.';
            return null;
        }

        $tmpDir = storage_path('app/tmp/ocr/' . Str::uuid());
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0777, true);
        }

        $pdftoppm = $this->pdftoppmBinary();
        $pdftopng = $this->pdftopngBinary();
        $magick = $this->magickBinary();

        // 1) Preferir ImageMagick si está disponible: suele dar mejor resultado en escaneos (preprocesado).
        // En Windows, ImageMagick necesita Ghostscript para leer PDFs. Si no está, no lo intentamos.
        if ($this->isCommandAvailable($magick) && (PHP_OS_FAMILY !== 'Windows' || $this->isGhostscriptAvailable())) {
            $out = $tmpDir . DIRECTORY_SEPARATOR . 'page.png';

            // magick -density 300 input.pdf[0] -colorspace Gray -contrast-stretch 0x10% -sharpen 0x1 output.png
            // Mantenerlo simple para máxima compatibilidad en Windows/Linux.
            $process = new Process([
                $magick,
                '-density',
                '300',
                $absolutePdfPath . '[0]',
                '-alpha',
                'remove',
                '-alpha',
                'off',
                '-colorspace',
                'Gray',
                '-resize',
                '200%',
                '-contrast-stretch',
                '0x10%',
                '-sharpen',
                '0x1',
                '-quality',
                '95',
                $out,
            ]);
            $this->applyRemainingTimeout($process);
            $process->run();

            if ($process->isSuccessful() && is_file($out)) {
                return $out;
            }

            $this->appendProcessError($warnings, 'Falló la conversión PDF→imagen con ImageMagick.', $process);
        } elseif ($this->isCommandAvailable($magick) && PHP_OS_FAMILY === 'Windows') {
            // Solo como guía en local.
            try {
                if (function_exists('app') && app()->isLocal()) {
                    $warnings[] = 'Nota: ImageMagick en Windows requiere Ghostscript (gswin64c.exe) para convertir PDFs.';
                }
            } catch (\Throwable) {
                // noop
            }
        }

        // 2) Fallback: pdftopng (Xpdf) si existe (genera PNG directo).
        if ($this->isCommandAvailable($pdftopng)) {
            $prefix = $tmpDir . DIRECTORY_SEPARATOR . 'page';
            $process = new Process([$pdftopng, '-f', '1', '-l', '1', '-r', '600', '-gray', '-aa', 'yes', '-aaVector', 'yes', $absolutePdfPath, $prefix]);
            $this->applyRemainingTimeout($process);
            $process->run();

            if ($process->isSuccessful()) {
                // Xpdf suele generar page-000001.png (pero variamos por seguridad)
                $pngs = glob($tmpDir . DIRECTORY_SEPARATOR . 'page-*.png') ?: [];
                sort($pngs);
                if ($pngs !== [] && is_file($pngs[0])) {
                    return $pngs[0];
                }

                $alt = $prefix . '-1.png';
                if (is_file($alt)) {
                    return $alt;
                }
            }

            $this->appendProcessError($warnings, 'Falló la conversión PDF→imagen con pdftopng.', $process);
        }

        // 3) Fallback: pdftoppm si existe.
        // Nota: en Windows muchos instalan Xpdf (pdftoppm 4.xx) que NO soporta -png.
        // Tesseract puede leer PPM/PGM sin problema, así que generamos PPM/PGM por defecto.
        if ($this->isCommandAvailable($pdftoppm)) {
            $prefix = $tmpDir . DIRECTORY_SEPARATOR . 'page';
            $process = new Process([$pdftoppm, '-f', '1', '-l', '1', '-r', '600', $absolutePdfPath, $prefix]);
            $this->applyRemainingTimeout($process);
            $process->run();

            $ppm = $prefix . '-1.ppm';
            $pgm = $prefix . '-1.pgm';
            $pbm = $prefix . '-1.pbm';
            if ($process->isSuccessful()) {
                if (is_file($ppm)) {
                    return $ppm;
                }
                if (is_file($pgm)) {
                    return $pgm;
                }
                if (is_file($pbm)) {
                    return $pbm;
                }
            }

            $this->appendProcessError($warnings, 'Falló la conversión PDF→imagen con pdftoppm.', $process);
        }

        // Si llegamos aquí, no pudimos convertir el PDF a imagen.
        // Solo recomendamos instalación si no hay ningún binario disponible.
        if (!$this->isCommandAvailable($magick) && !$this->isCommandAvailable($pdftopng) && !$this->isCommandAvailable($pdftoppm)) {
            $warnings[] = 'Para OCR en PDFs escaneados necesitas instalar Poppler (pdftoppm) o ImageMagick (magick).';
        } else {
            $warnings[] = 'No pude convertir el PDF a una imagen para aplicar OCR.';
        }
        return null;
    }

    private function appendProcessError(array &$warnings, string $prefix, Process $process): void
    {
        $warnings[] = $prefix;

        try {
            if (function_exists('app') && app()->isLocal()) {
                $err = trim((string) $process->getErrorOutput());
                $out = trim((string) $process->getOutput());
                $msg = $err !== '' ? $err : $out;
                $msg = preg_replace('/\s+/u', ' ', (string) $msg) ?? $msg;
                $msg = mb_substr($msg, 0, 400);
                if (trim($msg) !== '') {
                    $warnings[] = 'Detalle: ' . $msg;
                }
            }
        } catch (\Throwable) {
            // noop
        }
    }

    private function pdftopngBinary(): string
    {
        $configured = (string) env('PDFTOPNG_BINARY', '');
        $configured = trim($configured);
        if ($configured !== '' && is_file($configured)) {
            return $configured;
        }

        // Heurística: si PDFTOPPM_BINARY apunta a un shim, intentamos el hermano pdftopng.exe
        $fromPpm = (string) env('PDFTOPPM_BINARY', '');
        $fromPpm = trim($fromPpm);
        if ($fromPpm !== '' && is_file($fromPpm)) {
            $candidate = str_ireplace('pdftoppm.exe', 'pdftopng.exe', $fromPpm);
            if ($candidate !== $fromPpm && is_file($candidate)) {
                return $candidate;
            }
        }

        return 'pdftopng';
    }

    private function ocrTextFromImage(string $absoluteImagePath, array &$warnings): ?string
    {
        if (!$this->hasTimeBudget()) {
            $warnings[] = 'OCR omitido: se agotó el tiempo máximo de análisis.';
            return null;
        }

        $tesseract = $this->tesseractBinary();
        if (!$this->isCommandAvailable($tesseract)) {
            $warnings[] = 'Para leer imágenes automáticamente necesitas instalar Tesseract OCR (comando: tesseract).';
            return null;
        }

        $tessdataDir = $this->tessdataDir();

        $inputs = [$absoluteImagePath];
        $tmpFiles = [];
        $tmpDir = null;

        // Si ImageMagick está disponible, generamos una variante más fácil de leer:
        // - gris + auto-level + deskew + upscale
        // - recorte centrado (en estas recetas la tabla suele estar al centro)
        $magick = $this->magickBinary();
        if ($this->isCommandAvailable($magick)) {
            try {
                $tmpDir = storage_path('app/tmp/ocr/pre/' . Str::uuid());
                if (!is_dir($tmpDir)) {
                    @mkdir($tmpDir, 0777, true);
                }

                // Generamos varios recortes: centro/norte para tabla, y sur por si DP u otros datos van al pie.
                $variants = [
                    ['name' => 'center.png', 'gravity' => 'Center', 'crop' => '92%x62%+0+0'],
                    ['name' => 'north.png', 'gravity' => 'North', 'crop' => '96%x45%+0+0'],
                    ['name' => 'northcenter.png', 'gravity' => 'North', 'crop' => '96%x55%+0+0'],
                    ['name' => 'south.png', 'gravity' => 'South', 'crop' => '96%x45%+0+0'],
                    ['name' => 'southcenter.png', 'gravity' => 'South', 'crop' => '96%x55%+0+0'],
                ];

                foreach ($variants as $v) {
                    $out = $tmpDir . DIRECTORY_SEPARATOR . $v['name'];
                    $process = new Process([
                        $magick,
                        $absoluteImagePath,
                        '-alpha',
                        'remove',
                        '-alpha',
                        'off',
                        '-colorspace',
                        'Gray',
                        '-auto-level',
                        '-deskew',
                        '40%',
                        '-filter',
                        'Lanczos',
                        '-resize',
                        '300%',
                        '-sharpen',
                        '0x1',
                        '-gravity',
                        $v['gravity'],
                        '-crop',
                        $v['crop'],
                        '+repage',
                        '-quality',
                        '95',
                        $out,
                    ]);
                    $this->applyRemainingTimeout($process);
                    $process->run();

                    if ($process->isSuccessful() && is_file($out)) {
                        $inputs[] = $out;
                        $tmpFiles[] = $out;
                    }
                }
            } catch (\Throwable) {
                // noop
            }
        }

        try {
            // OCR en recetas escaneadas varía muchísimo: probamos varias combinaciones y
            // nos quedamos con la salida "mejor" (más números/tokens útiles).
            $candidates = [];

            $attempts = [
                ['lang' => 'spa+eng', 'psm' => '6'],
                ['lang' => 'spa+eng', 'psm' => '4'],
                ['lang' => 'eng', 'psm' => '6'],
                ['lang' => 'eng', 'psm' => '4'],
            ];

            foreach ($inputs as $inputPath) {
                foreach ($attempts as $attempt) {
                    if (!$this->hasTimeBudget()) {
                        $warnings[] = 'OCR detenido por límite de tiempo.';
                        break 2;
                    }

                    $text = $this->runTesseract($tesseract, $inputPath, $attempt['lang'], $attempt['psm'], $tessdataDir, $warnings);
                    if ($text === null) {
                        continue;
                    }

                    $candidates[] = ['text' => $text, 'lang' => $attempt['lang'], 'psm' => $attempt['psm']];

                    // Early-exit: si ya hay suficientes dígitos y señales típicas de receta, no seguimos gastando CPU.
                    $score = $this->ocrScore($text);
                    $digits = preg_match_all('/\d/u', $text) ?: 0;
                    $hasRxSignals = preg_match('/\b(OD|OI|OS|DER(ECHO)?|IZQ(UIERDO)?|ESF(ERICO)?|CIL(INDRICO)?|EJE|AXIS|LEJOS|CERCA|DP|PD|DISTANCIA\s+PUPILAR)\b/iu', $text) === 1;
                    // Requiere valores de receta: NEUTRO/PLANO o un número con signo (incluye -075) y algún eje 1-3 dígitos.
                    $hasSphLike = preg_match('/\b(NEUTRO|PLANO|PLANA)\b/iu', $text) === 1 || preg_match('/[+\-]\s*(?:\d+[\.,]\d+|[\.,]\d+|\d{3})\b/u', $text) === 1;
                    $hasAxisLike = preg_match('/\b(\d{1,3})\b/u', $text) === 1;

                    // Solo cortamos si ya hay señales claras de AMBOS ojos.
                    $hasRight = preg_match('/\b(DERECHO|DER|OD)\b/iu', $text) === 1;
                    $hasLeft = preg_match('/\b(IZQUIERDO|IZQ|OI|OS)\b/iu', $text) === 1;

                    // No cortamos temprano si aún no aparecen señales de DP (a veces está más abajo en la hoja).
                    $hasDpSignals = preg_match('/\b(DP|PD|DNP|DISTANCIA\s+PUPILAR)\b/iu', $text) === 1
                        || preg_match('/\b\d{2}\s*[\/\-]\s*\d{2}\b/u', $text) === 1;

                    if ($digits >= 10 && $hasRxSignals && $hasSphLike && $hasAxisLike && $hasRight && $hasLeft && $hasDpSignals && $score >= 380) {
                        $warnings[] = 'OCR: corte temprano (resultado suficiente).';
                        return $text;
                    }
                }
            }

            if ($candidates === []) {
                $warnings[] = 'Tesseract no pudo procesar la imagen.';
                return null;
            }

            usort($candidates, fn ($a, $b) => $this->ocrScore($b['text']) <=> $this->ocrScore($a['text']));
            $best = $candidates[0];

            $digits = preg_match_all('/\d/u', (string) $best['text']) ?: 0;
            if ($digits < 6) {
                $warnings[] = 'OCR: texto con pocos números detectados; puede ser un escaneo borroso o con baja resolución.';
            }

            $warnings[] = 'OCR: usando mejor resultado (lang=' . $best['lang'] . ', psm=' . $best['psm'] . ').';
            return (string) $best['text'];
        } finally {
            foreach ($tmpFiles as $f) {
                try {
                    if (is_string($f) && $f !== '' && is_file($f)) {
                        @unlink($f);
                    }
                } catch (\Throwable) {
                    // noop
                }
            }
            try {
                if (is_string($tmpDir) && $tmpDir !== '' && is_dir($tmpDir)) {
                    @rmdir($tmpDir);
                }
            } catch (\Throwable) {
                // noop
            }
        }
    }

    private function runTesseract(
        string $tesseract,
        string $absoluteImagePath,
        string $lang,
        string $psm,
        ?string $tessdataDir,
        array &$warnings,
    ): ?string {
        $args = [
            $tesseract,
            $absoluteImagePath,
            'stdout',
            '-l',
            $lang,
            '--psm',
            $psm,
            '--dpi',
            '300',
            '--oem',
            '1',
            '-c',
            'preserve_interword_spaces=1',
        ];

        if ($tessdataDir !== null) {
            $args[] = '--tessdata-dir';
            $args[] = $tessdataDir;
        }

        $process = new Process($args);
        $this->applyRemainingTimeout($process);
        $process->run();

        if ($process->isSuccessful()) {
            return (string) $process->getOutput();
        }

        $err = (string) $process->getErrorOutput();
        if ($lang !== 'eng' && (stripos($err, 'Error opening data file') !== false || stripos($err, 'Failed loading language') !== false)) {
            // Solo advertir una vez (evita spam de warnings)
            $already = false;
            foreach ($warnings as $w) {
                if (is_string($w) && stripos($w, 'faltan datos de idioma') !== false) {
                    $already = true;
                    break;
                }
            }
            if (!$already) {
                $warnings[] = 'Tesseract está instalado, pero faltan datos de idioma (spa).';
            }
        }

        return null;
    }

    private function hasTimeBudget(): bool
    {
        return $this->remainingSeconds() > 0;
    }

    private function remainingSeconds(): int
    {
        if ($this->analysisDeadlineAt === null) {
            return self::MAX_ANALYSIS_SECONDS;
        }

        return (int) floor($this->analysisDeadlineAt - microtime(true));
    }

    private function applyRemainingTimeout(Process $process): void
    {
        $remaining = $this->remainingSeconds();
        $timeout = max(self::MIN_PROCESS_SECONDS, $remaining);
        $process->setTimeout($timeout);
    }

    private function ocrScore(string $text): int
    {
        $digits = preg_match_all('/\d/u', $text) ?: 0;
        $signals = 0;
        if (preg_match('/\b(OD|OI|OS|DER(ECHO)?|IZQ(UIERDO)?|ESF|CIL|EJE|DP|PD|LEJOS|CERCA)\b/iu', $text)) {
            $signals += 30;
        }
        if (preg_match('/[+\-]\s*\d/u', $text)) {
            $signals += 10;
        }

        // Bonus si hay líneas tipo "DERECHO NEUTRO -0.75 180"
        if (preg_match('/\b(DERECHO|DER|OD)\b[\s\S]{0,80}?\b(NEUTRO|PLANO|PLANA|[+\-]?(?:\d+(?:[\.,]\d+)?|[\.,]\d+|\d{3}))\b[\s\S]{0,80}?\b([+\-]?(?:\d+(?:[\.,]\d+)?|[\.,]\d+|\d{3}))\b[\s\S]{0,40}?\b(\d{1,3})\b/iu', $text)) {
            $signals += 80;
        }
        if (preg_match('/\b(IZQUIERDO|IZQ|OI|OS)\b[\s\S]{0,80}?\b(NEUTRO|PLANO|PLANA|[+\-]?(?:\d+(?:[\.,]\d+)?|[\.,]\d+|\d{3}))\b[\s\S]{0,80}?\b([+\-]?(?:\d+(?:[\.,]\d+)?|[\.,]\d+|\d{3}))\b[\s\S]{0,40}?\b(\d{1,3})\b/iu', $text)) {
            $signals += 80;
        }
        return (int) (mb_strlen($text) + ($digits * 5) + $signals);
    }

    private function isGhostscriptAvailable(): bool
    {
        // Windows: gswin64c/gswin32c. Linux: gs.
        if (PHP_OS_FAMILY === 'Windows') {
            return $this->isCommandAvailable('gswin64c') || $this->isCommandAvailable('gswin32c');
        }

        return $this->isCommandAvailable('gs');
    }

    private function tessdataDir(): ?string
    {
        $dir = (string) env('TESSERACT_TESSDATA_DIR', '');
        $dir = trim($dir);
        if ($dir === '') {
            return null;
        }

        // Normalizar separadores para Windows/Linux.
        $dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dir);

        return is_dir($dir) ? $dir : null;
    }

    private function tesseractBinary(): string
    {
        $configured = (string) env('TESSERACT_BINARY', '');
        $configured = trim($configured);
        if ($configured !== '' && is_file($configured)) {
            return $configured;
        }

        return 'tesseract';
    }

    private function isCommandAvailable(string $command): bool
    {
        try {
            // Si viene una ruta absoluta (ej: C:\\Program Files\\...\\tesseract.exe), valida por existencia.
            if (str_contains($command, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\/u', $command) === 1) {
                return is_file($command);
            }

            if (PHP_OS_FAMILY === 'Windows') {
                $process = new Process(['where', $command]);
            } else {
                // En Linux/Unix, "command" suele ser builtin del shell, no un ejecutable.
                // Usamos sh para que funcione en contenedores (Render) de forma portable.
                $process = new Process(['sh', '-lc', 'command -v ' . escapeshellarg($command)]);
            }
            $process->setTimeout(5);
            $process->run();
            return $process->isSuccessful() && trim($process->getOutput()) !== '';
        } catch (\Throwable) {
            return false;
        }
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[\t\x0B\f]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/[ ]{2,}/u', ' ', $text) ?? $text;

        // Normaliza guiones/minus unicode típicos del OCR.
        $text = str_replace(["−", "–", "—", "‐", "‑"], '-', $text);

        // Correcciones típicas OCR para números: O/0.
        // Ej: "O.75" -> "0.75", "+O.50" -> "+0.50"
        $text = preg_replace('/\b([+\-]?)O([\.,]\d+)/u', '$10$2', $text) ?? $text;
        $text = preg_replace('/\b([+\-]?)O(\d)/u', '$10$2', $text) ?? $text;
        $text = preg_replace('/\b([+\-]?\d)O([\.,]\d+)/u', '$10$2', $text) ?? $text;

        // Ej: 18O -> 180, 6O -> 60
        $text = preg_replace('/(?<=\d)[oO](?=\d)/u', '0', $text) ?? $text;
        $text = preg_replace('/(?<=\d)[oO]\b/u', '0', $text) ?? $text;

        // Correcciones típicas OCR para números: I/l -> 1 (ej: I80 -> 180)
        $text = preg_replace('/(?<=\d)[Il](?=\d)/u', '1', $text) ?? $text;
        $text = preg_replace('/\b[Il](?=\d)/u', '1', $text) ?? $text;

        // Algunos PDFs separan letras con espacios: D P, E S F, etc.
        $text = preg_replace('/\bD\s*P\b/u', 'DP', $text) ?? $text;
        $text = preg_replace('/\bE\s*S\s*F\b/u', 'ESF', $text) ?? $text;
        $text = preg_replace('/\bC\s*I\s*L\b/u', 'CIL', $text) ?? $text;
        $text = preg_replace('/\bE\s*J\s*E\b/u', 'EJE', $text) ?? $text;
        $text = preg_replace('/\bA\s*D\s*D\b/u', 'ADD', $text) ?? $text;

        // LEJOS / CERCA a veces vienen separados o con O/0.
        $text = preg_replace('/\bL\s*E\s*J\s*O\s*S\b/iu', 'LEJOS', $text) ?? $text;
        $text = preg_replace('/\bC\s*E\s*R\s*C\s*A\b/iu', 'CERCA', $text) ?? $text;
        $text = preg_replace('/\bLE[IJLT1][O0]S\b/iu', 'LEJOS', $text) ?? $text;
        // Casos vistos en OCR: "Lesós" / "LESOS".
        $text = preg_replace('/\bLES[ÓO]S\b/iu', 'LEJOS', $text) ?? $text;

        // OCR típico de dioptrías: "-04 75" -> "-0.75" (se cuela un 4 antes de los cuartos).
        // Importante: no usamos \b antes del signo porque '-' no es word-char.
        $text = preg_replace('/(?<!\d)([+\-])0?4\s*(00|25|50|75)\b/u', '${1}0.${2}', $text) ?? $text;

        // Correcciones típicas OCR en español (tablas de receta)
        // DERECHO / IZQUIERDO
        $text = preg_replace('/\bCERECHO\b/iu', 'DERECHO', $text) ?? $text;
        $text = preg_replace('/\bDERECHS\b/iu', 'DERECHO', $text) ?? $text;
        $text = preg_replace('/\bDEFRECHS\b/iu', 'DERECHO', $text) ?? $text;
        $text = preg_replace('/\bDERFCHO\b/iu', 'DERECHO', $text) ?? $text;
        $text = preg_replace('/\bDER\s*ECHO\b/iu', 'DERECHO', $text) ?? $text;
        $text = preg_replace('/\bDERE\s*CHO\b/iu', 'DERECHO', $text) ?? $text;

        $text = preg_replace('/\bLEGUTERDG\b/iu', 'IZQUIERDO', $text) ?? $text;
        $text = preg_replace('/\bLEGUIERDO\b/iu', 'IZQUIERDO', $text) ?? $text;
        $text = preg_replace('/\bISQUIERDO\b/iu', 'IZQUIERDO', $text) ?? $text;
        // Casos vistos en OCR: "LEU ERDG" y variantes.
        $text = preg_replace('/\bLEU\s*ERD[GC]\b/iu', 'IZQUIERDO', $text) ?? $text;
        // A veces aparece como "[SQUIERDO"/"SQUIERDO".
        $text = preg_replace('/\b\[?SQUIERDO\b/iu', 'IZQUIERDO', $text) ?? $text;
        $text = preg_replace('/\bIZQUI\s*ERDO\b/iu', 'IZQUIERDO', $text) ?? $text;
        $text = preg_replace('/\bIZQ\s*UIERDO\b/iu', 'IZQUIERDO', $text) ?? $text;

        // NEUTRO suele salir como "NESTED"/"HEIFTRO"/"NEIFTRO".
        $text = preg_replace('/\bNESTED\b/iu', 'NEUTRO', $text) ?? $text;
        $text = preg_replace('/\bHEIFTRO\b/iu', 'NEUTRO', $text) ?? $text;
        $text = preg_replace('/\bNEIFTRO\b/iu', 'NEUTRO', $text) ?? $text;
        $text = preg_replace('/\bNEU\s*TRO\b/iu', 'NEUTRO', $text) ?? $text;
        // Casos vistos en OCR: "SETE"/"HECTES".
        $text = preg_replace('/\bSETE\b/iu', 'NEUTRO', $text) ?? $text;
        $text = preg_replace('/\bHECTES\b/iu', 'NEUTRO', $text) ?? $text;

        return $text;
    }

    private function extractEye(string $text, array $eyeTokens): array
    {
        $snippet = $this->findSnippetForEye($text, $eyeTokens);

        $sph = $this->extractNumberAfterLabel($snippet, ['ESF', 'ESFERICO', 'ESFÉRICO', 'SPH', 'SPHERE']);
        $cyl = $this->extractNumberAfterLabel($snippet, ['CIL', 'CILINDRICO', 'CILÍNDRICO', 'CYL', 'CYLINDER']);
        $axis = $this->extractIntAfterLabel($snippet, ['EJE', 'AX', 'AXIS']);
        $add = $this->extractNumberAfterLabel($snippet, ['ADD', 'ADICION', 'ADICIÓN']);

        // A veces viene como: OD -1.00 -0.50 180
        if ($sph === null || $cyl === null || $axis === null) {
            $fallback = $this->extractThreeColumnNumbers($snippet);
            $sph ??= $fallback['sph'];
            $cyl ??= $fallback['cyl'];
            $axis ??= $fallback['axis'];
        }

        return [
            'sph' => $this->normalizeFloat($sph),
            'cyl' => $this->normalizeFloat($cyl),
            'axis' => $this->normalizeAxis($axis),
            'add' => $this->normalizeFloat($add),
        ];
    }

    private function findSnippetForEye(string $text, array $eyeTokens): string
    {
        // Busca una línea o bloque corto alrededor del token.
        // Usamos [\s\S] para atravesar saltos de línea (OCR suele meterlos).
        $pattern = '/\b(' . implode('|', array_map(static fn ($t) => preg_quote($t, '/'), $eyeTokens)) . ')\b[\s\S]{0,220}/iu';
        if (preg_match($pattern, $text, $m)) {
            return (string) ($m[0] ?? '');
        }

        return $text;
    }

    private function extractNumberAfterLabel(string $text, array $labels): ?string
    {
        $labelPattern = implode('|', array_map(static fn ($l) => preg_quote($l, '/'), $labels));

        // Captura números tipo: -1.25, -1,25, +.50, .50
        // o palabras típicas de receta: NEUTRO/PLANO.
        $pattern = '/\b(?:' . $labelPattern . ')\b\s*[:=]?\s*(NEUTRO|PLANO|PLANA|[+-]?(?:\d+(?:[\.,]\d+)?|[\.,]\d+))/iu';
        if (preg_match($pattern, $text, $m)) {
            return (string) ($m[1] ?? null);
        }

        return null;
    }

    private function extractIntAfterLabel(string $text, array $labels): ?int
    {
        $labelPattern = implode('|', array_map(static fn ($l) => preg_quote($l, '/'), $labels));
        $pattern = '/\b(?:' . $labelPattern . ')\b\s*[:=]?\s*(\d{1,3})\b/iu';
        if (preg_match($pattern, $text, $m)) {
            $v = (int) ($m[1] ?? 0);
            return $v > 0 ? $v : null;
        }

        return null;
    }

    private function extractThreeColumnNumbers(string $snippet): array
    {
        // Intenta extraer tres columnas de números en el fragmento.
        // Ej: "OD -1.00 -0.50 180" o "-1.00 -0.50 180".
        $pattern = '/([+-]?(?:\d+(?:[\.,]\d+)?|[\.,]\d+))\s+([+-]?(?:\d+(?:[\.,]\d+)?|[\.,]\d+))\s+(\d{1,3})\b/u';
        if (!preg_match($pattern, $snippet, $m)) {
            return ['sph' => null, 'cyl' => null, 'axis' => null];
        }

        return [
            'sph' => (string) ($m[1] ?? null),
            'cyl' => (string) ($m[2] ?? null),
            'axis' => isset($m[3]) ? (int) $m[3] : null,
        ];
    }

    private function extractDp(string $text): array
    {
        $binocular = null;
        $od = null;
        $oi = null;

        // Distancia pupilar (muchas recetas no dicen DP sino la frase completa)
        if (preg_match('/DISTANCIA\s+PUPILAR\b\s*[:=]?\s*(\d{2}(?:[\.,]\d+)?)\s*[\/\-]\s*(\d{2}(?:[\.,]\d+)?)\b/iu', $text, $m)) {
            $od = $this->normalizeFloat((string) ($m[1] ?? null));
            $oi = $this->normalizeFloat((string) ($m[2] ?? null));
        }

        // Algunas OCR pierden el separador y queda como "62 64" (solo espacios)
        if ($od === null && $oi === null
            && preg_match('/DISTANCIA\s+PUPILAR\b\s*[:=]?\s*(\d{2})\s+(\d{2})\b/iu', $text, $m)) {
            $od = $this->normalizeFloat((string) ($m[1] ?? null));
            $oi = $this->normalizeFloat((string) ($m[2] ?? null));
        }

        // DP por ojo: DP 31/32
        if (preg_match('/\b(?:DP|PD|DNP)\b\s*[:=]?\s*(\d{2}(?:[\.,]\d+)?)\s*[\/\-]\s*(\d{2}(?:[\.,]\d+)?)\b/iu', $text, $m)) {
            $od = $this->normalizeFloat((string) ($m[1] ?? null));
            $oi = $this->normalizeFloat((string) ($m[2] ?? null));
        }

        // DP por ojo: DP 31 32 (solo espacios)
        if ($od === null && $oi === null
            && preg_match('/\b(?:DP|PD|DNP)\b\s*[:=]?\s*(\d{2})\s+(\d{2})\b/iu', $text, $m)) {
            $od = $this->normalizeFloat((string) ($m[1] ?? null));
            $oi = $this->normalizeFloat((string) ($m[2] ?? null));
        }

        // Algunas OCR pierden el separador y queda como 6204 (62/64)
        if (($od === null || $oi === null) && preg_match('/DISTANCIA\s+PUPILAR\b\s*[:=]?\s*(\d{4})\b/iu', $text, $m)) {
            $raw = (string) ($m[1] ?? '');
            $left = substr($raw, 0, 2);
            $right = substr($raw, 2, 2);
            $od = $this->normalizeFloat($left) ?? $od;
            $oi = $this->normalizeFloat($right) ?? $oi;

            // Heurística: si el segundo par es inválido (ej 04) pero el primero es razonable (ej 62),
            // probablemente se perdió el primer dígito (64). Ajuste: +60.
            if ($od !== null && $oi !== null && $od >= 40 && $od <= 80 && $oi < 20) {
                $oi = $oi + 60;
            }
        }

        if (($od === null || $oi === null) && preg_match('/\b(?:DP|PD|DNP)\b\s*[:=]?\s*(\d{4})\b/u', $text, $m)) {
            $raw = (string) ($m[1] ?? '');
            $left = substr($raw, 0, 2);
            $right = substr($raw, 2, 2);
            $od = $this->normalizeFloat($left) ?? $od;
            $oi = $this->normalizeFloat($right) ?? $oi;
            if ($od !== null && $oi !== null && $od >= 40 && $od <= 80 && $oi < 20) {
                $oi = $oi + 60;
            }
        }

        // Si ya tenemos por-ojo, no reportamos binocular.
        if ($od !== null || $oi !== null) {
            $binocular = null;
        }

        // Binocular por frase completa.
        if ($od === null && $oi === null
            && preg_match('/DISTANCIA\s+PUPILAR\b\s*[:=]?\s*(\d{2}(?:[\.,]\d+)?)\b/iu', $text, $m)) {
            $binocular = $this->normalizeFloat((string) ($m[1] ?? null));
        }

        // DP / PD binocular: DP 63 (evita capturar el primer número de "31/32")
        if ($od === null && $oi === null
            && preg_match('/\b(?:DP|PD|DNP)\b\s*[:=]?\s*(\d{2}(?:[\.,]\d+)?)\b(?!\s*[\/\-])/iu', $text, $m)) {
            $binocular = $this->normalizeFloat((string) ($m[1] ?? null));
        }

        return [
            'binocular' => $binocular,
            'od' => $od,
            'oi' => $oi,
        ];
    }

    private function extractLejosTable(string $text): array
    {
        $out = [
            'od' => ['sph' => null, 'cyl' => null, 'axis' => null, 'add' => null],
            'oi' => ['sph' => null, 'cyl' => null, 'axis' => null, 'add' => null],
        ];

        $block = $text;
        if (preg_match('/\bLEJOS\b([\s\S]{0,700})\bCERCA\b/iu', $text, $m)) {
            $block = (string) ($m[1] ?? $text);
        }

        // Preferimos capturar solo la línea/"fila" de cada ojo para no mezclar OD con OI.
        $snipOd = '';
        if (preg_match('/\b(?:DERECHO|OD|DER)\b[^\n]{0,200}/iu', $block, $m)) {
            $snipOd = (string) ($m[0] ?? '');
        } elseif (preg_match('/\b(?:DERECHO|OD|DER)\b[\s\S]{0,120}/iu', $block, $m)) {
            $snipOd = (string) ($m[0] ?? '');
        }

        $snipOi = '';
        if (preg_match('/\b(?:IZQUIERDO|OI|OS|IZQ)\b[^\n]{0,200}/iu', $block, $m)) {
            $snipOi = (string) ($m[0] ?? '');
        } elseif (preg_match('/\b(?:IZQUIERDO|OI|OS|IZQ)\b[\s\S]{0,120}/iu', $block, $m)) {
            $snipOi = (string) ($m[0] ?? '');
        }

        $parsedOd = null;
        $parsedOi = null;

        foreach ([
            'od' => $snipOd,
            'oi' => $snipOi,
        ] as $key => $row) {
            if (trim($row) === '') {
                continue;
            }

            $parsed = $this->extractLejosRow($row);
            if ($key === 'od') {
                $parsedOd = $parsed;
            } else {
                $parsedOi = $parsed;
            }
            $out[$key] = $this->mergeEye($out[$key], $parsed);
        }

        // Heurística: a veces el OCR pierde el '-' en un ojo (ej: "0,75" en vez de "-0,75").
        // Si ambos ojos tienen misma magnitud y uno viene explícitamente negativo y el otro no tiene signo,
        // forzamos el otro a negativo. Nunca toca valores con '+' explícito.
        $out = $this->fixMissingCylMinusFromPair($out, $parsedOd, $parsedOi);

        return $out;
    }

    private function fixMissingCylMinusFromPair(array $out, ?array $parsedOd, ?array $parsedOi): array
    {
        $odCyl = $out['od']['cyl'] ?? null;
        $oiCyl = $out['oi']['cyl'] ?? null;
        if (!is_float($odCyl) || !is_float($oiCyl)) {
            return $out;
        }

        $odSign = $parsedOd['_cyl_sign'] ?? null;
        $oiSign = $parsedOi['_cyl_sign'] ?? null;

        // Solo si magnitudes son prácticamente iguales.
        if (abs(abs($odCyl) - abs($oiCyl)) > 0.001) {
            return $out;
        }

        // Caso: OI es explícitamente negativo y OD no tiene signo y quedó positivo.
        if ($oiCyl < 0 && $oiSign === '-' && $odSign === null && $odCyl > 0) {
            $out['od']['cyl'] = -abs($odCyl);
        }

        // Caso inverso: OD explícitamente negativo y OI sin signo quedó positivo.
        if ($odCyl < 0 && $odSign === '-' && $oiSign === null && $oiCyl > 0) {
            $out['oi']['cyl'] = -abs($oiCyl);
        }

        return $out;
    }

    private function extractLejosRow(string $row): array
    {
        $row = str_replace(["\r\n", "\r"], "\n", $row);

        // Quita fracciones de agudeza visual tipo 20/50.
        $row = preg_replace('/\b\d{1,2}\s*\/\s*\d{1,3}\b/u', ' ', $row) ?? $row;

        // O. o O, como 0.
        $row = preg_replace('/\bO(?=[\.,]\s)/u', '0', $row) ?? $row;
        $row = preg_replace('/\bO\.(?=\s)/u', '0.', $row) ?? $row;

        // NEUTRO / PLANO => 0
        $row = preg_replace('/\b(NEUTRO|PLANO|PLANA)\b/iu', '0', $row) ?? $row;

        // Caso típico OCR: "-04 75" => "-0.75" (solo cuando hay 04 explícito).
        $row = preg_replace('/([+\-])\s*0\s*4\s*(00|25|50|75)\b/u', '$10.$2', $row) ?? $row;

        // Primero intenta columnas limpias.
        $fallback = $this->extractThreeColumnNumbers($row);
        if ($fallback['sph'] !== null || $fallback['cyl'] !== null || $fallback['axis'] !== null) {
            return [
                'sph' => $this->normalizeFloat($fallback['sph']),
                'cyl' => $this->normalizeFloat($fallback['cyl']),
                'axis' => $this->normalizeAxis($fallback['axis']),
                'add' => null,
            ];
        }

        $sph = null;
        if (preg_match('/\b0\b/u', $row) === 1) {
            $sph = 0.0;
        }

        $cyl = null;
        $cylSign = null;
        // Primero: con signo explícito.
        if (preg_match('/\b([+\-])\s*(\d+(?:[\.,]\d+)?|[\.,]\d+)\b/u', $row, $m)) {
            $cylSign = (string) ($m[1] ?? null);
            $cyl = $this->normalizeFloat(($cylSign ?? '') . (string) ($m[2] ?? ''));
        } elseif (preg_match('/\b(\d+[\.,]\d+|[\.,]\d+)\b/u', $row, $m)) {
            // Fallback: sin signo (OCR pudo perder '-').
            $cyl = $this->normalizeFloat((string) ($m[1] ?? null));
            $cylSign = null;
        }

        $axis = $this->extractAxisFromRow($row);

        // Si no hay nada útil, no inventamos.
        if ($sph === null && $cyl === null && $axis === null) {
            return ['sph' => null, 'cyl' => null, 'axis' => null, 'add' => null];
        }

        return [
            'sph' => $sph,
            'cyl' => $cyl,
            'axis' => $axis,
            'add' => null,
            // meta interno (no se expone; mergeEye lo ignora)
            '_cyl_sign' => $cylSign,
        ];
    }

    private function extractAxisFromRow(string $row): ?int
    {
        // Evita capturar partes de decimales (ej: "0.75" => "75").
        $tmp = preg_replace('/[+\-]?\d+(?:[\.,]\d+)+/u', ' ', $row) ?? $row;
        $tmp = preg_replace('/\b\d{1,2}\s*\/\s*\d{1,3}\b/u', ' ', $tmp) ?? $tmp;

        $ints = [];
        if (preg_match_all('/\b(\d{1,3})\b/u', $tmp, $mm)) {
            foreach (($mm[1] ?? []) as $cand) {
                $v = (int) $cand;
                if ($v >= 0 && $v <= 180) {
                    $ints[] = $v;
                }
            }
        }

        if ($ints === []) {
            return null;
        }

        // Preferimos ejes de 3 dígitos (100-180) si existen.
        foreach ($ints as $v) {
            if ($v >= 100 && $v <= 180) {
                return $v;
            }
        }

        // Si solo hay 2 dígitos, tomamos el último candidato.
        return end($ints) ?: null;
    }

    private function extractByColumns(string $text): array
    {
        // Busca patrones donde OD/OI estén en líneas y luego 3 columnas.
        $out = [
            'od' => ['sph' => null, 'cyl' => null, 'axis' => null, 'add' => null],
            'oi' => ['sph' => null, 'cyl' => null, 'axis' => null, 'add' => null],
        ];

        $sphToken = '(?:NEUTRO|PLANO|PLANA|[+-]?(?:\d+(?:[\.,]\d+)?|[\.,]\d+))';
        $cylToken = '([+-]?(?:\d+(?:[\.,]\d+)?|[\.,]\d+))';
        $axisToken = '(\d{1,3})';

        $patterns = [
            // También aparecen como DERECHO/IZQUIERDO en algunas recetas.
            // OCR puede meter saltos de línea entre tokens/valores, por eso usamos [\s\S].
            'od' => '/\b(?:OD|DERECHO|DER)\b[\s\S]{0,140}?(' . $sphToken . ')\s+' . $cylToken . '\s+' . $axisToken . '\b/iu',
            'oi' => '/\b(?:OI|OS|IZQUIERDO|IZQ)\b[\s\S]{0,140}?(' . $sphToken . ')\s+' . $cylToken . '\s+' . $axisToken . '\b/iu',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $out[$key]['sph'] = $this->normalizeFloat((string) ($m[1] ?? null));
                $out[$key]['cyl'] = $this->normalizeFloat((string) ($m[2] ?? null));
                $out[$key]['axis'] = $this->normalizeAxis(isset($m[3]) ? (int) $m[3] : null);
            }
        }

        return $out;
    }

    private function normalizeFloat(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $v = trim($value);
        if ($v === '') {
            return null;
        }

        $upper = mb_strtoupper($v);
        if (in_array($upper, ['NEUTRO', 'PLANO', 'PLANA'], true)) {
            return 0.0;
        }

        // OCR típico: -075 en vez de -0.75, 050 en vez de 0.50, 125 en vez de 1.25
        if (preg_match('/^[+\-]?\d{3}$/u', $v) === 1) {
            $sign = '';
            if (str_starts_with($v, '+') || str_starts_with($v, '-')) {
                $sign = $v[0];
                $v = substr($v, 1);
            }
            $v = $sign . substr($v, 0, 1) . '.' . substr($v, 1, 2);
        }

        $v = str_replace(',', '.', $v);
        if (str_starts_with($v, '.')) {
            $v = '0' . $v;
        }
        if (str_starts_with($v, '+.')) {
            $v = '+0' . substr($v, 1);
        }
        if (str_starts_with($v, '-.')) {
            $v = '-0' . substr($v, 1);
        }

        $num = filter_var($v, FILTER_VALIDATE_FLOAT);
        if ($num === false) {
            return null;
        }

        return (float) $num;
    }

    private function normalizeAxis(?int $axis): ?int
    {
        if ($axis === null) {
            return null;
        }

        $axis = max(0, min(180, $axis));
        return $axis;
    }

    private function isEmptyEye(array $eye): bool
    {
        return ($eye['sph'] ?? null) === null
            && ($eye['cyl'] ?? null) === null
            && ($eye['axis'] ?? null) === null
            && ($eye['add'] ?? null) === null;
    }

        private function pdftoppmBinary(): string
        {
            $configured = (string) env('PDFTOPPM_BINARY', '');
            $configured = trim($configured);
            if ($configured !== '' && is_file($configured)) {
                return $configured;
            }

            return 'pdftoppm';
        }

        private function magickBinary(): string
        {
            $configured = (string) env('MAGICK_BINARY', '');
            $configured = trim($configured);
            if ($configured !== '' && is_file($configured)) {
                return $configured;
            }

            return 'magick';
        }

    private function mergeEye(array $base, array $incoming): array
    {
        return [
            'sph' => $base['sph'] ?? ($incoming['sph'] ?? null),
            'cyl' => $base['cyl'] ?? ($incoming['cyl'] ?? null),
            'axis' => $base['axis'] ?? ($incoming['axis'] ?? null),
            'add' => $base['add'] ?? ($incoming['add'] ?? null),
        ];
    }

    private function pickBestEye(array $base, array $candidate): array
    {
        if ($this->isEmptyEye($candidate)) {
            return $base;
        }

        if ($this->isEmptyEye($base)) {
            return $candidate;
        }

        // Caso muy común en OCR: "-04 75" se parsea como CIL=-4 y EJE=75.
        // Si el base parece ese error y el candidato trae un CYL más razonable (ej -0.75), preferimos candidato.
        if ($this->isLikelySplitDecimalEye($base)) {
            $candCyl = $candidate['cyl'] ?? null;
            $baseCyl = $base['cyl'] ?? null;

            if (is_float($candCyl) && (abs($candCyl) < 3.0)
                && is_float($baseCyl) && abs($baseCyl) >= 3.0) {
                return [
                    'sph' => $candidate['sph'] ?? $base['sph'],
                    'cyl' => $candCyl,
                    // Si el eje del base era sospechoso, preferimos el del candidato; si no existe, lo dejamos en null.
                    'axis' => $candidate['axis'] ?? null,
                    'add' => $candidate['add'] ?? $base['add'],
                ];
            }
        }

        // Por defecto: solo completa lo que falte.
        return $this->mergeEye($base, $candidate);
    }

    private function isLikelySplitDecimalEye(array $eye): bool
    {
        $sph = $eye['sph'] ?? null;
        $cyl = $eye['cyl'] ?? null;
        $axis = $eye['axis'] ?? null;

        if (!is_float($sph) || !is_float($cyl) || !is_int($axis)) {
            return false;
        }

        // NEUTRO + CYL entero grande + eje 25/50/75 suele indicar un decimal partido (0.75 => 75).
        if (abs($sph) > 0.001) {
            return false;
        }

        $isIntegerCyl = abs($cyl - round($cyl)) < 0.0001;
        if (!$isIntegerCyl) {
            return false;
        }

        if (abs($cyl) < 3.0 || abs($cyl) > 8.0) {
            return false;
        }

        return in_array($axis, [25, 50, 75], true);
    }
}
