<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScanSource;
use App\Services\Scanner\IndexerService;
use App\Models\ScanRun;

class ScanController extends Controller
{
    public function start(Request $request, IndexerService $service)
    {
        $source_id = $request->input('source_id');
        $sync = (bool) $request->input('sync', true);

        $source = ScanSource::findOrFail($source_id);
        if ($sync) {
            $run = $service->scan($source, ['triggered_by'=>'api']);
            return response()->json(['scan_run_id' => $run->id, 'status'=>$run->status]);
        } else {
            // dispatch to queue — left as TODO
            $run = \App\Models\ScanRun::create(['scan_source_id'=>$source->id,'status'=>'pending','triggered_by'=>'api']);
            // dispatch job to process
            return response()->json(['scan_run_id'=>$run->id,'status'=>$run->status]);
        }
    }

    public function show($run)
    {
        $r = ScanRun::findOrFail($run);
        return response()->json($r);
    }
}
