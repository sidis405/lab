<?php

namespace App\Commands;

use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class Update extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'update';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Update sites from Git repo';


    protected $site;
    protected $sites;
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $content = Storage::get('sites');

        $this->info("OFFICINE06 Lab v0.1");
        $this->line("Choose between these git managed projects:");

        $counter = 1;

        foreach (explode("\n", $content) as $row) {
            if (strlen($row) > 5) {
                $params = explode("    ", $row);

                if ($params[3] == "y") {
                    $this->sites[] = [$counter, $params[0]];
                }

                $counter++;
            }
        }


        $headers = ['ID', 'Hostname'];

        $this->table($headers, $this->sites);
        $this->site = $this->ask('Choose the ID of project to update from git:');
        $this->site = $this->sites[$this->site - 1][1];

        foreach (explode("\n", $content) as $row) {
            if (strlen($row) > 5) {
                $params = explode("    ", $row);

                if ($params[0] == $this->site) {
                    passthru("cd " . $params[2] . "; git pull origin " .$params[5]);
                }
            }
        }
    }
}
