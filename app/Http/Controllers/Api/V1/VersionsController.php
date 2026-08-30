<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FileRecord;
use App\Models\FileVersion;
use Illuminate\Http\Request;
use App\Services\Storage\OriginalFileStorage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VersionsController extends Controller
{
    public function index(FileRecord $file)
    {
        $versions = $file->versions()->get();
        return response()->json($versions);
    }

    public function downloadOriginal(FileRecord $file, $version, OriginalFileStorage $storage)
    {
        $ver = $file->versions()->where('version_number', $version)->firstOrFail();
        if (!$ver->blob) {
            abort(404, 'Original blob not found');
        }
        $blob = $ver->blob;

        // check permissions here (left as TODO)
        $stream = $storage->streamBlob($blob);
        $filename = $ver->original_file_name ?? ($file->basename ?: 'download.bin');

        return new StreamedResponse(function() use ($stream) {
            while (!feof($stream)) {
                echo fread($stream, 8192);
            }
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $file->mime_type ?? 'application/octet-stream',
            'Content-Length' => $blob->size_bytes,
            'Content-Disposition' => 'attachment; filename="'.basename($filename).'"',
            'X-Content-SHA256' => $blob->sha256,
        ]);
    }

    public function extractedText(FileRecord $file, $version)
    {
        $ver = $file->versions()->where('version_number', $version)->firstOrFail();
        if ($ver->extracted_text) {
            return response()->json(['extracted_text' => $ver->extracted_text, 'status'=>$ver->extraction_status]);
        }

        return response()->json(['extracted_text' => null, 'status'=>$ver->extraction_status], 204);
    }
}
