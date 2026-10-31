<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case USER = 'user';
    case LOCAL_MODERATOR = 'local_moderator';
    case SUPER_ADMIN = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::USER => 'Kullanıcı',
            self::LOCAL_MODERATOR => 'Bölge Moderatörü',
            self::SUPER_ADMIN => 'Süper Yönetici',
        };
    }
}

