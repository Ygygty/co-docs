<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileText extends Model
{
    public $timestamps = false;

    protected $fillable = ['file_id','file_version_id','content','updated_at'];

    public function file()
    {
        return $this->belongsTo(FileRecord::class);
    }

    public function version()
    {
        return $this->belongsTo(FileVersion::class, 'file_version_id');
    }
}
