<?php
namespace App\Services\Normalizer;

class TextNormalizer
{
    public function normalize(string $text): string
    {
        // remove BOM
        $text = preg_replace('/^\x{FEFF}/u', '', $text);

        // normalize line endings to \n
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // NFC
        $text = normalizer_normalize($text, \Normalizer::FORM_C) ?: $text;

        // do NOT lowercase Arabic by default; leave spaces
        return $text;
    }
}
