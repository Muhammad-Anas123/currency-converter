<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CurrencyService;

class ClearCurrencyCache extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'currency:clear-cache';

    /**
     * The console command description.
     */
    protected $description = 'Clear all currency exchange rate caches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing currency caches...');
        
        $service = new CurrencyService();
        $result = $service->clearCache();
        
        if ($result['success']) {
            $this->info('✓ ' . $result['message']);
        } else {
            $this->error('✗ ' . $result['message']);
        }

        return 0;
    }
}