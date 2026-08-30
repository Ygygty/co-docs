<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScanSource;
use App\Services\Scanner\IndexerService;

class IndexScanCommand extends Command
{
    protected $signature = 'indexer:scan
        {--source= : scan_source id or name}
        {--path= : single path to scan (overrides source.root_path if provided)}
        {--queue : dispatch jobs to queue (not implemented in this simplified command)}
        {--sync : run synchronously}
        {--force : force reindex}
        {--dry-run : do not write DB or storage}
        {--limit= : limit files}
        {--sleep= : sleep between files}
    ';

    protected $description = 'Scan index sources and create versions';

    public function handle(IndexerService $service)
    {
        $sourceOpt = $this->option('source');
        if ($sourceOpt) {
            $source = ScanSource::where('id', $sourceOpt)->orWhere('name', $sourceOpt)->first();
            if (!$source) {
                $this->error("Source not found: {$sourceOpt}");
                return 1;
            }
        } else {
            // take all active sources and scan sequentially
            $sources = ScanSource::where('is_active', true)->get();
            foreach ($sources as $s) {
                $this->info("Scanning source {$s->name}");
                $service->scan($s, ['triggered_by'=>'cli']);
            }
            return 0;
        }

        $this->info("Scanning source {$source->name}");
        $service->scan($source, ['triggered_by'=>'cli']);
        $this->info("Done.");
        return 0;
    }
}
