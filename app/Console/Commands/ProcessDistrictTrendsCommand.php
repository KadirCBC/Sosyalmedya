<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Analytics\DistrictPulseService;
use Illuminate\Console\Command;

class ProcessDistrictTrendsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:process-trends {--location= : Belirli bir lokasyon ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'İlçelerdeki son 24 saatin gönderilerini analiz ederek trend kelimeleri, etiketleri ve duygu endeksini hesaplar';

    /**
     * Execute the console command.
     */
    public function handle(DistrictPulseService $pulseService): int
    {
        $this->info('🚀 Yerel Nabız & İlçe Trend Analiz Motoru Başlatılıyor...');

        $locationId = $this->option('location') ? (int) $this->option('location') : null;

        $pulseService->processDailyDistrictAnalytics($locationId);

        $this->info('✅ Trend analizi ve ilçe memnuniyet endeksleri başarıyla güncellendi.');

        return self::SUCCESS;
    }
}

