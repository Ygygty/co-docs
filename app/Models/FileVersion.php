<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileVersion extends Model
{
    public $timestamps = false; // created_at used manually

    protected $fillable = [
        'file_id','version_number','is_current','raw_blob_id','raw_sha256','raw_size_bytes',
        'original_file_name','original_extension','content_sha256','extracted_text_size',
        'detected_encoding','extraction_method','extractor_version','extraction_status',
        'extracted_text_storage_path','extracted_text','storage_status','purged_at','meta','created_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
        'purged_at' => 'datetime',
    ];

    public function file()
    {
        return $this->belongsTo(FileRecord::class, 'file_id');
    }

    public function blob()
    {
        return $this->belongsTo(FileBlob::class, 'raw_blob_id');
    }
}
