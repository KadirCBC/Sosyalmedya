<?php

declare(strict_types=1);

namespace App\Enums;

enum AdStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Onay Bekliyor',
            self::ACTIVE => 'Yayında',
            self::REJECTED => 'Reddedildi',
            self::EXPIRED => 'Süresi Doldu',
        };
    }
}

