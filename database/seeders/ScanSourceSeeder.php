<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScanSource;

class ScanSourceSeeder extends Seeder
{
    public function run()
    {
        ScanSource::create([
            'name' => 'local-samples',
            'root_path' => base_path('tests/fixtures'),
            'is_recursive' => true,
            'include_patterns' => [],
            'exclude_patterns' => [],
            'allowed_extensions' => ['txt','md','log','csv','docx'],
            'max_file_size_bytes' => 50 * 1024 * 1024,
            'max_versions' => 200,
            'is_active' => true,
        ]);
    }
}
