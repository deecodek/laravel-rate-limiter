<?php

namespace Deecodek\RateLimiter\Commands;

use Illuminate\Console\Command;
use Deecodek\RateLimiter\Models\RateLimitRule;
use Illuminate\Support\Facades\File;

class ImportRulesCommand extends Command
{
    protected $signature = 'rate-limit:import-rules {file : Path to JSON file containing rules}';

    protected $description = 'Import rate limit rules from JSON file';

    public function handle()
    {
        $file = $this->argument('file');
        
        if (!File::exists($file)) {
            $this->error("File {$file} does not exist");
            return;
        }
        
        $content = File::get($file);
        $rules = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Invalid JSON in file: " . json_last_error_msg());
            return;
        }
        
        $imported = 0;
        foreach ($rules as $ruleData) {
            RateLimitRule::updateOrCreate(
                ['name' => $ruleData['name']],
                $ruleData
            );
            $imported++;
        }
        
        $this->info("Imported {$imported} rules successfully");
    }
}