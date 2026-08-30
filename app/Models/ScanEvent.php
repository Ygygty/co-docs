<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['scan_run_id','file_id','file_version_id','event_type','message','meta','created_at'];

    protected $casts = ['meta' => 'array','created_at' => 'datetime'];

    public function run() { return $this->belongsTo(ScanRun::class,'scan_run_id'); }
}
