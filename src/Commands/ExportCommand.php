<?php

namespace Deecodek\RateLimiter\Commands;

use Illuminate\Console\Command;
use Deecodek\RateLimiter\Models\RateLimitAttempt;
use Deecodek\RateLimiter\Models\RateLimitRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ExportCommand extends Command
{
    protected $signature = 'rate-limit:export 
                            {--start= : Start date (Y-m-d)}
                            {--end= : End date (Y-m-d)}
                            {--format=csv : Export format (csv|json)}
                            {--file= : Output file name}';

    protected $description = 'Export rate limit data';

    public function handle()
    {
        $start = $this->option('start') ? Carbon::parse($this->option('start')) : Carbon::now()->subDay();
        $end = $this->option('end') ? Carbon::parse($this->option('end')) : Carbon::now();
        $format = $this->option('format');
        $file = $this->option('file') ?: "rate-limit-export-{$start->format('Y-m-d')}-to-{$end->format('Y-m-d')}.{$format}";
        
        $this->info("Exporting data from {$start->toDateTimeString()} to {$end->toDateTimeString()}");
        
        $attempts = RateLimitAttempt::whereBetween('ts', [$start, $end])->get();
        
        if ($format === 'json') {
            $this->exportToJson($attempts, $file);
        } else {
            $this->exportToCsv($attempts, $file);
        }
        
        $this->info("Data exported to {$file}");
    }
    
    protected function exportToJson($attempts, $file)
    {
        $data = $attempts->toArray();
        Storage::put($file, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    protected function exportToCsv($attempts, $file)
    {
        $handle = fopen(storage_path("app/{$file}"), 'w');
        
        // Headers
        fputcsv($handle, ['ts', 'ip', 'user_id', 'route', 'method', 'weight', 'decision']);
        
        // Data
        foreach ($attempts as $attempt) {
            fputcsv($handle, [
                $attempt->ts,
                $attempt->ip,
                $attempt->user_id,
                $attempt->route,
                $attempt->method,
                $attempt->weight,
                $attempt->decision,
            ]);
        }
        
        fclose($handle);
    }
}