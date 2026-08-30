<?php
namespace App\Enums;

enum ExtractionStatus: string {
    case Pending = 'pending';
    case Success = 'success';
    case Failed = 'failed';
    case Skipped = 'skipped';
    case Unsupported = 'unsupported';
}
