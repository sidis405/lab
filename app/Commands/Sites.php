<?php

namespace App\Commands;

use Illuminate\Support\Facades\Storage;
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

        $this->info("OFFICINE06 Lab v0.1");
        $this->line("Your Webroot: " . env('WEBROOT'));

        foreach (explode("\n", $content) as $row) {
            if (strlen($row) > 5) {
                $row = str_replace(env('WEBROOT'), '[WEBROOT]/', $row);
                $sites[] = explode("    ", $row);
            }
        }

        $headers = ['Subdomain', 'Type', 'Webroot', 'Git', 'Repo', 'Branch', 'Creation Date'];

        $this->table($headers, $sites);
    }
}
