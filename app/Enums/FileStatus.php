<?php
namespace App\Enums;

enum FileStatus: string {
    case Active = 'active';
    case Missing = 'missing';
    case Error = 'error';
    case Unsupported = 'unsupported';
    case Disabled = 'disabled';
}
