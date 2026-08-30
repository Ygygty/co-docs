<?php
namespace App\Services\Extractors;

use ZipArchive;

class DocxTextExtractor implements TextExtractorInterface
{
    public function supports(string $extension, ?string $mimeType = null): bool
    {
        return strtolower($extension) === 'docx';
    }

    public function extract(string $absolutePath): ExtractionResult
    {
        $res = new ExtractionResult('success');

        $zip = new ZipArchive();
        $open = $zip->open($absolutePath);
        if ($open !== true) {
            $res->status = 'failed';
            $res->warnings[] = "Cannot open DOCX as Zip (code {$open})";
            return $res;
        }

        // read document.xml
        $index = $zip->locateName('word/document.xml');
        if ($index === false) {
            $res->status = 'failed';
            $res->warnings[] = 'document.xml not found inside docx';
            $zip->close();
            return $res;
        }

        $xml = $zip->getFromIndex($index);
        $zip->close();

        if (!$xml) {
            $res->status = 'failed';
            $res->warnings[] = 'Empty document.xml';
            return $res;
        }

        // parse XML and extract text nodes inside <w:t>
        $dom = new \DOMDocument();
        // suppress warnings for bad xml
        @ $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $nodes = $xpath->query('//w:t | //w:tab | //w:br');
        $parts = [];
        foreach ($nodes as $node) {
            if ($node->localName === 'tab') {
                $parts[] = "\t";
            } elseif ($node->localName === 'br') {
                $parts[] = "\n";
            } else {
                $parts[] = $node->nodeValue;
            }
        }

        $res->extractedText = implode('', $parts);
        return $res;
    }
}
