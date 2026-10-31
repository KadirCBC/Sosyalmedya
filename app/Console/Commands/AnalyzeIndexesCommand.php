<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class AnalyzeIndexesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:analyze-indexes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kritik posts ve locations sorguları üzerinde EXPLAIN analizi yaparak indeks verimliliğini doğrular';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== MySQL EXPLAIN ve İndeks Performans Analiz Aracı ===');
        $this->newLine();

        $queries = [
            '1. Yerel İlçe Akışı (Local Feed)' => "EXPLAIN SELECT id, user_id, content, scope, location_id, likes_count, comments_count FROM posts WHERE is_hidden = 0 AND scope = 'local' AND location_id = 1 ORDER BY id DESC LIMIT 20",
            '2. Genel Akış (General Feed)' => "EXPLAIN SELECT id, user_id, content, scope, location_id, likes_count, comments_count FROM posts WHERE is_hidden = 0 AND scope = 'general' ORDER BY id DESC LIMIT 20",
            '3. Kullanıcı Profil Akışı (User Timeline)' => "EXPLAIN SELECT id, content, created_at FROM posts WHERE is_hidden = 0 AND user_id = 1 ORDER BY id DESC LIMIT 20",
            '4. Lokasyon Arama (Location Lookup)' => "EXPLAIN SELECT id, city, district, latitude, longitude FROM locations WHERE city = 'Ankara' AND district = 'Keçiören' LIMIT 1",
            '5. Yorum Akışı (Post Comments)' => "EXPLAIN SELECT id, user_id, content, parent_id, likes_count FROM comments WHERE post_id = 1 ORDER BY id ASC LIMIT 50",
        ];

        foreach ($queries as $label => $sql) {
            $this->comment("👉 $label");
            $this->line("SQL: <fg=gray>$sql</>");

            try {
                $explainResults = DB::select($sql);

                $headers = ['id', 'select_type', 'table', 'type', 'possible_keys', 'key', 'key_len', 'ref', 'rows', 'filtered', 'Extra'];
                $rows = [];

                foreach ($explainResults as $row) {
                    $rowArray = (array) $row;
                    $extra = $rowArray['Extra'] ?? '';

                    // Uyarı veya başarı kontrolleri
                    $type = $rowArray['type'] ?? '';
                    $keyUsed = $rowArray['key'] ?? 'NULL';

                    if ($type === 'ALL' || $keyUsed === 'NULL') {
                        $this->error("  [!] DİKKAT: Full Table Scan tespit edildi! İndeks kullanılmıyor.");
                    } elseif (str_contains($extra, 'Using filesort')) {
                        $this->warn("  [!] DİKKAT: 'Using filesort' tespit edildi. Sıralama disk/bellek üzerinde yapılıyor.");
                    } else {
                        $this->info("  [✓] BAŞARILI: '$keyUsed' indeksi tam verimle kullanılıyor (Zero Filesort).");
                    }

                    $rows[] = [
                        $rowArray['id'] ?? '',
                        $rowArray['select_type'] ?? '',
                        $rowArray['table'] ?? '',
                        $rowArray['type'] ?? '',
                        $rowArray['possible_keys'] ?? '',
                        $rowArray['key'] ?? '',
                        $rowArray['key_len'] ?? '',
                        $rowArray['ref'] ?? '',
                        $rowArray['rows'] ?? '',
                        $rowArray['filtered'] ?? '',
                        $rowArray['Extra'] ?? '',
                    ];
                }

                $this->table($headers, $rows);
            } catch (Throwable $e) {
                $this->warn("  [i] Veritabanı bağlantısı veya tablo henüz oluşturulmamış: " . $e->getMessage());
            }

            $this->newLine();
        }

        $this->info('Analiz tamamlandı. Canlı sunucuda tüm sorguların "ref" veya "range" tipinde olduğundan ve "key" sütununda ilgili indeksin yer aldığından emin olun.');

        return self::SUCCESS;
    }
}

