<?php
namespace App\Services\Extractors;

class ExtractionResult
{
    public string $status; // success|failed|unsupported|skipped
    public ?string $extractedText = null;
    public ?string $detectedEncoding = null;
    public array $warnings = [];
    public array $meta = [];

    public function __construct(string $status = 'success')
    {
        $this->status = $status;
    }
}
