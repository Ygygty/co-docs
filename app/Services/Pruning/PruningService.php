<?php
namespace App\Services\Pruning;

use App\Models\FileRecord;
use App\Models\FileVersion;
use App\Models\FileBlob;
use Illuminate\Support\Facades\DB;

class PruningService
{
    public function pruneIfNeeded(FileRecord $file): void
    {
        $max = $file->max_versions ?? $file->scanSource->max_versions ?? config('indexer.versioning.max_versions_per_file', 200);
        $count = $file->versions()->count();
        if ($count <= $max) {
            return;
        }
        $policy = config('indexer.versioning.prune_policy', 'delete_oldest');

        $toRemove = $file->versions()->orderBy('version_number')->take($count - $max)->get();

        foreach ($toRemove as $version) {
            if ($policy === 'delete_oldest') {
                // detach blob if no longer referenced
                DB::transaction(function() use ($version) {
                    $blob = $version->blob;
                    $version->delete();

                    if ($blob) {
                        $refs = $blob->versions()->count();
                        if ($refs === 0) {
                            // remove storage and DB record
                            \Storage::disk(config('indexer.storage.disk'))->delete($blob->storage_path);
                            $blob->delete();
                        }
                    }
                    // record scan_event if needed (left to caller)
                });
            }
            // other policies can be implemented (archive_oldest, keep_metadata_only)
        }
    }
}
