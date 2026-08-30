<?php

use App\Models\ScanSource;
use Illuminate\Support\Facades\Storage;
use App\Services\Scanner\IndexerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a file and a version for new file', function () {
    // prepare fixture
    $fixture = base_path('tests/fixtures/sample1.txt');
    @mkdir(dirname($fixture), 0755, true);
    file_put_contents($fixture, "hello world");

    $source = ScanSource::create([
        'name'=>'ft',
        'root_path'=>base_path('tests/fixtures'),
        'is_recursive'=>true,
        'allowed_extensions'=>['txt'],
    ]);

    $svc = app(IndexerService::class);
    $run = $svc->scan($source, []);
    $this->assertDatabaseHas('files', ['scan_source_id'=>$source->id, 'basename'=>'sample1.txt']);
    $file = \App\Models\FileRecord::where('basename','sample1.txt')->first();
    $this->assertTrue($file->versions()->count() === 1);
});
