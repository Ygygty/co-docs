<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileBlob extends Model
{
    protected $fillable = [
        'sha256','size_bytes','mime_type','storage_path','last_verified_at','meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'last_verified_at' => 'datetime',
    ];

    public function versions()
    {
        return $this->hasMany(FileVersion::class, 'raw_blob_id');
    }
}
