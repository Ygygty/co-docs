<?php
namespace App\Services\Storage;

use App\Models\FileBlob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class OriginalFileStorage
{
    protected string $disk;
    protected string $blobsPath;
    protected int $copyRetries;

    public function __construct()
    {
        $this->disk = config('indexer.storage.disk', 'private');
        $this->blobsPath = config('indexer.storage.blobs_path', 'indexer/blobs');
        $this->copyRetries = config('indexer.concurrency.copy_retries', 3);
    }

    /**
     * Store file into blob storage, deduplicating if enabled.
     * Returns FileBlob model.
     */
    public function storeBlobFromPath(string $sourcePath, string $sha256): FileBlob
    {
        $dedup = (bool) config('indexer.versioning.deduplicate_blobs', true);

        // Try find existing
        if ($dedup) {
            $existing = FileBlob::where('sha256', $sha256)->first();
            if ($existing) {
                return $existing;
            }
        }

        // Advisory lock to avoid races when creating blob with same sha
        DB::statement('SELECT pg_advisory_xact_lock(hashtext(?))', [$sha256]);

        // check again inside lock
        if ($dedup) {
            $existing = FileBlob::where('sha256', $sha256)->first();
            if ($existing) {
                return $existing;
            }
        }

        // compute storage path e.g. indexer/blobs/aa/bb/sha.bin
        $prefix1 = substr($sha256, 0, 2);
        $prefix2 = substr($sha256, 2, 2);
        $storagePath = trim($this->blobsPath, '/') . "/{$prefix1}/{$prefix2}/{$sha256}.bin";

        // Ensure directory exists
        $tempStream = fopen($sourcePath, 'rb');
        if (!$tempStream) {
            throw new Exception('Cannot open source for reading: ' . $sourcePath);
        }

        $tmp = tmpfile();
        // Write to a temp file in local FS, then move to disk via stream to ensure atomic
        $written = false;
        $attempt = 0;
        while ($attempt < $this->copyRetries && !$written) {
            $attempt++;
            rewind($tempStream);
            // write to storage via putStream
            $success = Storage::disk($this->disk)->put($storagePath, $tempStream);
            if ($success) {
                $written = true;
                break;
            }
        }
        fclose($tempStream);
        if (!$written) {
            throw new Exception("Failed to copy file to storage after {$this->copyRetries} attempts");
        }

        // verify sha256 of stored file
        if ((bool) config('indexer.versioning.verify_after_copy', true)) {
            $stream = Storage::disk($this->disk)->readStream($storagePath);
            if ($stream === false) {
                // cleanup?
                throw new Exception('Cannot open stored blob for verification');
            }
            $ctx = hash_init('sha256');
            while (!feof($stream)) {
                hash_update($ctx, fread($stream, 8192));
            }
            fclose($stream);
            $storedSha = hash_final($ctx);
            if (!hash_equals($sha256, $storedSha)) {
                // try retries or throw
                Storage::disk($this->disk)->delete($storagePath);
                throw new Exception("Verification failed, expected {$sha256} got {$storedSha}");
            }
        }

        // create DB record
        $size = Storage::disk($this->disk)->size($storagePath);
        $blob = FileBlob::create([
            'sha256' => $sha256,
            'size_bytes' => $size,
            'storage_path' => $storagePath,
            'mime_type' => null,
            'meta' => [],
        ]);

        return $blob;
    }

    /**
     * Stream blob to output (for downloads).
     */
    public function streamBlob(FileBlob $blob)
    {
        $stream = Storage::disk($this->disk)->readStream($blob->storage_path);
        if ($stream === false) {
            throw new \Exception('Stored blob not found');
        }
        return $stream;
    }
}
