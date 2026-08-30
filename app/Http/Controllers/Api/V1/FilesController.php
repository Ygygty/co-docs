<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FileRecord;
use App\Http\Resources\FileResource;

class FilesController extends Controller
{
    public function index(Request $request)
    {
        $q = FileRecord::query();
        if ($request->filled('status')) $q->where('status', $request->status);
        if ($request->filled('extension')) $q->where('extension', $request->extension);
        if ($request->filled('source_id')) $q->where('scan_source_id', $request->source_id);
        if ($request->filled('q')) {
            $term = $request->q;
            $q->where('basename', 'ILIKE', "%{$term}%")->orWhere('relative_path', 'ILIKE', "%{$term}%");
        }
        $perPage = $request->get('per_page', 20);
        return FileResource::collection($q->paginate($perPage));
    }

    public function show(FileRecord $file)
    {
        return new FileResource($file->load('currentVersion'));
    }
}
