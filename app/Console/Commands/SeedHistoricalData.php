<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\PollsAndVotesSeeder;

class SeedHistoricalData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:historical-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with 6 months of historical polls and votes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to seed historical poll and vote data...');
        
        // Run the seeder
        $seeder = new PollsAndVotesSeeder();
        $seeder->setContainer(app())->setCommand($this);
        $seeder->run();
        
        $this->info('Historical data seeding complete!');
        
        return Command::SUCCESS;
    }
}
