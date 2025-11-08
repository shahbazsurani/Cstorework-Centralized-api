<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Models\Location;

class GenerateModelHashes extends Command
{
    protected $signature = 'generate:hashes {model=all}';
    protected $description = 'Generate hash values for models that are missing them';

    public function handle()
    {
        $model = $this->argument('model');

        if ($model === 'all' || $model === 'Location') {
            $this->generateLocationHashes();
        }

        $this->info('Hash generation complete!');
    }

    private function generateLocationHashes()
    {
        $this->info('Generating hashes for Location model...');

        if (!Schema::hasColumn('locations', 'hash')) {
            $this->error('Hash column does not exist in locations table!');
            return;
        }

        $locations = Location::withoutGlobalScope('active')
            ->whereNull('hash')
            ->orWhere('hash', '')
            ->get();

        $count = 0;
        foreach ($locations as $location) {
            $location->hash = bin2hex(random_bytes(127));
            $location->saveQuietly(); // Save without firing events
            $count++;
        }

        $this->info("Generated {$count} hash(es) for Location model.");
    }
}
