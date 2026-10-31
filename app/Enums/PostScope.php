<?php

declare(strict_types=1);

namespace App\Enums;

enum PostScope: string
{
    case LOCAL = 'local';
    case GENERAL = 'general';
}

