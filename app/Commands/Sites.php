<?php

namespace App\Commands;

use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Sites extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'sites';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Show currently configured sites';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $content = Storage::get('sites');

        $sites = [];

        foreach (explode("\n", $content) as $row) {
            if (strlen($row) > 5) {
                $sites[] = explode("    ", $row);
            }
        }

        $headers = ['Subdomain', 'Type', 'Webroot', 'Creation Date'];

        $this->table($headers, $sites);
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
