<?php
namespace App\Services\Extractors;

use App\Services\Extractors\ExtractionResult;

interface TextExtractorInterface
{
    public function supports(string $extension, ?string $mimeType = null): bool;

    /**
     * @param string $absolutePath
     * @return ExtractionResult
     */
    public function extract(string $absolutePath): ExtractionResult;
}
