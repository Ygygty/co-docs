<?php
namespace App\Services\Scanner;

use App\Models\ScanSource;
use App\Models\ScanRun;
use App\Models\FileRecord;
use App\Models\ScanEvent;
use App\Services\Versioning\VersionManager;
use Illuminate\Support\Facades\Log;

class IndexerService
{
    protected VersionManager $versionManager;

    public function __construct(VersionManager $vm)
    {
        $this->versionManager = $vm;
    }

    /**
     * $options: ['path'=>..., 'limit'=>..., 'dry_run'=>bool, 'queue'=>bool, ...]
     */
    public function scan(ScanSource $source, array $options = []): ScanRun
    {
        $run = ScanRun::create([
            'scan_source_id' => $source->id,
            'status' => 'running',
            'triggered_by' => $options['triggered_by'] ?? 'cli',
            'started_at' => now(),
        ]);

        $root = $source->root_path;
        if (!is_dir($root) || !is_readable($root)) {
            $run->status = 'failed';
            $run->error_message = "Root path not found or not readable: {$root}";
            $run->finished_at = now();
            $run->save();
            return $run;
        }

        $iterator = $this->lazyFileIterator($root, $source);

        $stats = ['seen'=>0,'created'=>0,'updated'=>0,'unchanged'=>0,'unsupported'=>0,'errors'=>0];
        foreach ($iterator as $filePath) {
            $stats['seen']++;
            try {
                $this->processFile($source, $filePath, $run, $options);
            } catch (\Throwable $e) {
                Log::error('Index error: '.$e->getMessage(), ['path'=>$filePath]);
                $stats['errors']++;
                ScanEvent::create([
                    'scan_run_id' => $run->id,
                    'event_type' => 'error',
                    'message' => $e->getMessage(),
                    'meta' => ['path'=>$filePath],
                ]);
            }
        }

        $run->listing_completed = true;
        $run->stats = $stats;
        $run->status = 'completed';
        $run->finished_at = now();
        $run->save();

        // mark missing files: (only if listing_completed true) — simplified: set files not seen since this run start to missing
        FileRecord::where('scan_source_id', $source->id)
            ->where(function($q) use ($run) {
                $q->whereNull('last_seen_at')->orWhere('last_seen_at', '<', $run->started_at);
            })->update(['status' => 'missing']);

        return $run;
    }

    protected function lazyFileIterator(string $root, ScanSource $source): \Generator
    {
        $flags = \FilesystemIterator::SKIP_DOTS;
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, $flags), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($rii as $file) {
            if ($file->isDir()) continue;
            // default excluded names
            $basename = $file->getBasename();
            $excludes = array_merge($source->exclude_patterns ?? [], config('indexer.scan.default_exclude_patterns', []));
            foreach ($excludes as $ex) {
                if (str_contains($file->getPathname(), $ex) || $basename === $ex) {
                    continue 2;
                }
            }
            // extension filter
            $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
            $allowed = $source->allowed_extensions ?? config('indexer.supported_extensions');
            if ($allowed && !in_array($ext, $allowed, true)) {
                continue;
            }
            yield $file->getPathname();
        }
    }

    protected function processFile(ScanSource $source, string $absolutePath, $run, array $options = [])
    {
        $relative = ltrim(str_replace($source->root_path, '', $absolutePath), DIRECTORY_SEPARATOR);
        $stat = @stat($absolutePath);
        if (!$stat) {
            ScanEvent::create(['scan_run_id'=>$run->id,'event_type'=>'error','message'=>'stat failed','meta'=>['path'=>$absolutePath]]);
            return;
        }

        $raw_sha = $this->sha256Stream($absolutePath);
        $size = $stat['size'] ?? filesize($absolutePath);
        $mtime = isset($stat['mtime']) ? date('Y-m-d H:i:s', $stat['mtime']) : null;
        $inode = $stat['ino'] ?? null;
        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        $file = FileRecord::firstOrNew(['scan_source_id'=>$source->id,'relative_path'=>$relative]);
        $file->absolute_path = $absolutePath;
        $file->basename = basename($absolutePath);
        $file->extension = $ext;
        $file->size_bytes = $size;
        $file->file_mtime = $mtime;
        $file->inode = $inode;
        $file->last_seen_at = now();
        $file->status = 'active';
        if (!$file->exists) {
            $file->save();
            // create version
            $this->versionManager->createVersion($file, $raw_sha, $size, $absolutePath);
            ScanEvent::create(['scan_run_id'=>$run->id,'file_id'=>$file->id,'event_type'=>'created','meta'=>['sha'=>$raw_sha]]);
        } else {
            if ($file->raw_sha256 === $raw_sha) {
                // unchanged
                $file->last_indexed_at = now();
                $file->save();
                ScanEvent::create(['scan_run_id'=>$run->id,'file_id'=>$file->id,'event_type'=>'unchanged','meta'=>['sha'=>$raw_sha]]);
            } else {
                // updated
                $this->versionManager->createVersion($file, $raw_sha, $size, $absolutePath);
                ScanEvent::create(['scan_run_id'=>$run->id,'file_id'=>$file->id,'event_type'=>'updated','meta'=>['sha'=>$raw_sha]]);
            }
        }
    }

    protected function sha256Stream(string $path): string
    {
        $ctx = hash_init('sha256');
        $handle = fopen($path, 'rb');
        if (!$handle) {
            throw new \RuntimeException('Cannot open file to hash: '.$path);
        }
        while (!feof($handle)) {
            $buf = fread($handle, 8192);
            if ($buf === false) break;
            hash_update($ctx, $buf);
        }
        fclose($handle);
        return hash_final($ctx);
    }
}
