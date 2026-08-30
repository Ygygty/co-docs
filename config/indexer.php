<?php

return [

    'versioning' => [
        'max_versions_per_file' => env('INDEXER_MAX_VERSIONS_PER_FILE', 200),
        'prune_policy' => env('INDEXER_PRUNE_POLICY', 'delete_oldest'),
        'deduplicate_blobs' => env('INDEXER_DEDUPLICATE_BLOBS', true),
        'verify_after_copy' => env('INDEXER_VERIFY_AFTER_COPY', true),
        'strict_raw_versioning' => env('INDEXER_STRICT_RAW_VERSIONING', true),
    ],

    'extraction' => [
        'enabled' => env('INDEXER_EXTRACTION_ENABLED', true),
        'store_extracted_text' => env('INDEXER_STORE_EXTRACTED_TEXT', true),
        'max_db_text_size_in_kb' => env('INDEXER_MAX_DB_TEXT_SIZE_KB', 512),
        'store_large_text_in_storage' => env('INDEXER_STORE_LARGE_TEXT_IN_STORAGE', true),
    ],

    'supported_extensions' => [
        'txt','md','log','csv','docx'
    ],

    'storage' => [
        'disk' => env('INDEXER_STORAGE_DISK', 'private'),
        'blobs_path' => env('INDEXER_BLOBS_PATH', 'indexer/blobs'),
        'originals_path' => env('INDEXER_ORIGINALS_PATH', 'indexer/originals'),
    ],

    'scan' => [
        'default_include_patterns' => [],
        'default_exclude_patterns' => ['.git','node_modules','vendor','.idea','.vscode'],
        'default_max_file_size_bytes' => env('INDEXER_DEFAULT_MAX_FILE_SIZE', 20 * 1024 * 1024),
    ],

    'concurrency' => [
        'copy_retries' => 3,
    ],
];
