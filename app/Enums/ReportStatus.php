<?php

declare(strict_types=1);

namespace App\Enums;

enum ReportStatus: string
{
    case PENDING = 'pending';
    case RESOLVED = 'resolved';
    case REJECTED = 'rejected';
}

