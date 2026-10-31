<?php

declare(strict_types=1);

namespace App\Enums;

enum ReportReason: string
{
    case SPAM = 'spam';
    case HATE_SPEECH = 'hate_speech';
    case HARASSMENT = 'harassment';
    case ILLEGAL = 'illegal';
    case COPYRIGHT = 'copyright';
}

