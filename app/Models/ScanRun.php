<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanRun extends Model
{
    protected $casts = [
        'stats' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function events()
    {
        return $this->hasMany(ScanEvent::class);
    }
}
