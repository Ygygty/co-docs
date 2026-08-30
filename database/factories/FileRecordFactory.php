<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\FileRecord;
use App\Models\ScanSource;

class FileRecordFactory extends Factory
{
    protected $model = FileRecord::class;

    public function definition()
    {
        return [
            'scan_source_id' => ScanSource::factory(),
            'relative_path' => $this->faker->word . '.txt',
            'absolute_path' => '/tmp/'.$this->faker->word.'.txt',
            'basename' => $this->faker->word . '.txt',
            'extension' => 'txt',
            'size_bytes' => 100,
            'file_mtime' => now(),
            'raw_sha256' => null,
            'status' => 'active',
        ];
    }
}
