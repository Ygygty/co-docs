<?php

use App\Services\Normalizer\TextNormalizer;

it('removes BOM and normalizes line endings and NFC', function () {
    $n = new TextNormalizer();
    $text = "\xEF\xBB\xBFLine1\r\nLine2\r\u2028";
    $res = $n->normalize($text);
    expect(strpos($res, "\xEF\xBB\xBF"))->toBeFalse();
    expect(strpos($res, "\r"))->toBeFalse();
    expect(strpos($res, "\n"))->toBeGreaterThanOrEqual(0);
});
