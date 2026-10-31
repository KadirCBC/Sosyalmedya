<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Veritabanı tohumlama ana sınıfı.
     */
    public function run(): void
    {
        // 1. Türkiye'nin tüm il ve ilçelerini tohumla
        $this->call(LocationSeeder::class);

        // Pilot ilçeleri al (Ankara Keçiören & Afyon Sandıklı)
        $kecioren = Location::query()->where('city', 'Ankara')->where('district', 'Keçiören')->first();
        $sandikli = Location::query()->where('city', 'Afyonkarahisar')->where('district', 'Sandıklı')->first();
        $defaultLocationId = $kecioren?->id ?? 1;

        // 2. Süper Yönetici Hesabı
        User::query()->firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Sistem Yöneticisi',
                'phone' => '905555555555',
                'phone_verified_at' => now(),
                'password' => Hash::make('Admin12345!'),
                'location_id' => $defaultLocationId,
                'role' => UserRole::SUPER_ADMIN,
                'bio' => 'Meydan platform yöneticisi.',
            ]
        );

        // 3. Resmi Duyuru Botu (Belediye & Kesinti Bülteni Hesabı)
        User::query()->firstOrCreate(
            ['username' => 'resmi_duyuru'],
            [
                'name' => 'Resmi Duyuru & Bülten Botu',
                'phone' => '905000000001',
                'phone_verified_at' => now(),
                'password' => Hash::make(bin2hex(random_bytes(16))),
                'location_id' => $defaultLocationId,
                'role' => UserRole::USER,
                'bio' => 'Pilot ilçeler için resmi belediye bültenleri, su/elektrik kesintileri ve yerel spor duyuruları.',
            ]
        );

        // 4. Mahalle Rehberi & Trend Botu
        User::query()->firstOrCreate(
            ['username' => 'mahalle_rehberi'],
            [
                'name' => 'Mahalle Rehberi',
                'phone' => '905000000002',
                'phone_verified_at' => now(),
                'password' => Hash::make(bin2hex(random_bytes(16))),
                'location_id' => $defaultLocationId,
                'role' => UserRole::USER,
                'bio' => 'Mahallende hayatı kolaylaştıran öneriler, yerel tartışmalar ve keşifler.',
            ]
        );
    }
}

