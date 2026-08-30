<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanSource extends Model
{
    protected $fillable = [
        'name','root_path','is_recursive','include_patterns','exclude_patterns',
        'allowed_extensions','max_file_size_bytes','max_versions','is_active','follow_symlinks'
    ];

    protected $casts = [
        'include_patterns' => 'array',
        'exclude_patterns' => 'array',
        'allowed_extensions' => 'array',
        'is_recursive' => 'boolean',
        'is_active' => 'boolean',
        'follow_symlinks' => 'boolean',
    ];

    public function files()
    {
        return $this->hasMany(FileRecord::class);
    }
}
