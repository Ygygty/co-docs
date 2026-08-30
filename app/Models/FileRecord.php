<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileRecord extends Model
{
    use SoftDeletes;

    protected $table = 'files';

    protected $fillable = [
        'scan_source_id','relative_path','absolute_path','basename','extension','mime_type',
        'size_bytes','file_mtime','inode','raw_sha256','current_content_sha256','status',
        'max_versions','last_seen_at','last_indexed_at','last_error_at','error_message','meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'last_seen_at' => 'datetime',
        'last_indexed_at' => 'datetime',
        'last_error_at' => 'datetime',
    ];

    public function scanSource()
    {
        return $this->belongsTo(ScanSource::class);
    }

    public function versions()
    {
        return $this->hasMany(FileVersion::class, 'file_id')->orderByDesc('version_number');
    }

    public function currentVersion()
    {
        return $this->hasOne(FileVersion::class, 'file_id')->where('is_current', true);
    }
}
