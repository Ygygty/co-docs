<?php
namespace App\Enums;

enum StorageStatus: string {
    case Pending = 'pending';
    case Stored = 'stored';
    case Verified = 'verified';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case Purged = 'purged';
}
