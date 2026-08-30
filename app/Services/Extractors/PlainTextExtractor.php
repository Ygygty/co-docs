<?php
namespace App\Services\Extractors;

use Exception;

class PlainTextExtractor implements TextExtractorInterface
{
    protected array $supported = ['txt','md','log','csv'];

    public function supports(string $extension, ?string $mimeType = null): bool
    {
        return in_array(strtolower($extension), $this->supported, true);
    }

    public function extract(string $absolutePath): ExtractionResult
    {
        $result = new ExtractionResult('success');

        $encodings = ['UTF-8','Windows-1256','ISO-8859-6','CP1252','ASCII','ISO-8859-1'];
        // read stream chunked
        $handle = @fopen($absolutePath, 'rb');
        if (!$handle) {
            $res = new ExtractionResult('failed');
            $res->warnings[] = 'Cannot open file';
            return $res;
        }

        // read small prefix to detect encoding
        $prefix = stream_get_contents($handle, 4096);
        rewind($handle);

        $detected = mb_detect_encoding($prefix, $encodings, true) ?: null;
        $result->detectedEncoding = $detected;

        // read whole by streaming but collect into string (ok for moderate sizes; for large allow storage option)
        $chunks = [];
        while (!feof($handle)) {
            $chunks[] = stream_get_contents($handle, 8192);
        }
        fclose($handle);

        $text = implode('', $chunks);

        if ($detected && strtoupper($detected) !== 'UTF-8') {
            // convert to UTF-8 for indexing only
            $converted = @iconv($detected, 'UTF-8//IGNORE', $text);
            if ($converted === false) {
                $result->status = 'failed';
                $result->warnings[] = "Encoding conversion failed from {$detected}";
                $result->extractedText = null;
                return $result;
            }
            $text = $converted;
        } else {
            // ensure valid UTF-8
            if (!mb_check_encoding($text, 'UTF-8')) {
                $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
            }
        }

        // basic normalizing: remove BOM will be handled by TextNormalizer later
        $result->extractedText = $text;

        return $result;
    }
}
