<?php
namespace App\Services\Versioning;

use App\Models\FileRecord;
use App\Models\FileVersion;
use App\Services\Storage\OriginalFileStorage;
use Illuminate\Support\Facades\DB;
use App\Services\Pruning\PruningService;

class VersionManager
{
    protected OriginalFileStorage $storage;
    protected PruningService $pruner;

    public function __construct(OriginalFileStorage $storage, PruningService $pruner)
    {
        $this->storage = $storage;
        $this->pruner = $pruner;
    }

    /**
     * Create a new version for given file. Assumes raw_sha256 and size known.
     * Returns created FileVersion or null on failure.
     */
    public function createVersion(FileRecord $file, string $rawSha256, int $sizeBytes, string $sourcePath, array $options = []): ?FileVersion
    {
        return DB::transaction(function () use ($file, $rawSha256, $sizeBytes, $sourcePath, $options) {
            // compute new version number
            $latest = $file->versions()->orderByDesc('version_number')->first();
            $nextVersion = $latest ? $latest->version_number + 1 : 1;

            // store blob (deduped inside)
            $blob = $this->storage->storeBlobFromPath($sourcePath, $rawSha256);

            // mark previous current false
            FileVersion::where('file_id', $file->id)->where('is_current', true)->update(['is_current' => false]);

            // create version record
            $v = FileVersion::create([
                'file_id' => $file->id,
                'version_number' => $nextVersion,
                'is_current' => true,
                'raw_blob_id' => $blob->id,
                'raw_sha256' => $rawSha256,
                'raw_size_bytes' => $sizeBytes,
                'original_file_name' => $file->basename,
                'original_extension' => $file->extension,
                'storage_status' => 'verified',
                'extraction_status' => 'pending',
                'created_at' => now(),
            ]);

            // update file's current_content_sha256 & raw_sha256
            $file->raw_sha256 = $rawSha256;
            $file->current_content_sha256 = $rawSha256;
            $file->last_indexed_at = now();
            $file->save();

            // prune if needed (pruner will record events)
            $this->pruner->pruneIfNeeded($file);

            return $v;
        });
    }
}
