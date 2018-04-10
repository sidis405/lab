<?php

namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class Make extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'make {subdomain : Subdomain of the new site}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Make a new site instance';

    protected $types;
    protected $type;
    protected $isGitManaged;
    protected $repository;
    protected $branch;

    protected $appends;
    protected $fullDomain;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info("OFFICINE06 Lab v0.1");

        $this->types = $this->getTemplates();

        $this->info("Setting up " . $this->argument('subdomain') . "." . env("BASE_DOMAIN"));
        $headers = ['ID', 'Template'];
        $this->table($headers, $this->types);

        $this->type = $this->ask('Choose the ID of project:');
        $this->type = $this->types[$this->type -1][1];

        $this->isGitManaged = $this->choice('Would you like to specify a git repository?', ['y', 'n'], 1);

        if ($this->isGitManaged == 'y') {
            $this->repository = $this->ask("Please paste in your repository url:");

            $this->branch = $this->ask('Specify a branch (default: master):');

            if (strlen($this->branch) < 2) {
                $this->branch = "master";
            }
        }

        $this->fullDomain = $this->argument('subdomain') . "." . env("BASE_DOMAIN");

        $this->appends = $this->ask('App a path append (ex: build):');

        if (strlen($this->appends) < 1) {
            $this->appends = "/";
        }

        Storage::append('sites', $this->makeRecord());

        $this->line("Created record entry");

        $this->makeWebroot();

        $this->line("Made webroot and copied boilerplate");

        $config = $this->makeConfig();

        $this->line("Parsed relevant config");

        $this->writeConfig($config);
        $this->line("Wrote config");
        $this->linkConfig($config);
        $this->line("Symlinked config");
        $this->restartNginx();
        $this->line("Nginx was restarted");

        $this->info("Project setup complete. You may reach it at http://" . $this->fullDomain);
    }

    private function restartNginx()
    {
        passthru("service nginx restart");
    }

    private function linkConfig()
    {
        passthru("ln -s " . env('NGINX_SITES_AVAILABLE') . '/' . $this->fullDomain . ' ' . env('NGINX_SITES_ENABLED'));
    }

    private function writeConfig($config)
    {
        $fp =  fopen(env('NGINX_SITES_AVAILABLE') . $this->fullDomain, 'w');
        fwrite($fp, $config);
        fclose($fp);
    }

    private function makeRecord()
    {
        return $this->fullDomain . '    '
        . $this->type . '    '
        . env('WEBROOT') . $this->fullDomain . '    '
        . $this->isGitManaged . '    '
        . $this->repository . '    '
        . $this->branch . '    '
        . Carbon::now()->format('Y-m-d H:i');
    }

    private function makeWebroot()
    {
        mkdir(env('WEBROOT') . $this->fullDomain, 0755, true);

        if ($this->isGitManaged == 'y') {
            passthru("git clone " . $this->repository . " " . env('WEBROOT') . $this->fullDomain);
        } else {
            $fp =  fopen(env('WEBROOT') . $this->fullDomain . '/index.html', 'w');
            fwrite($fp, str_replace("LAB_SERVER_NAME", $this->fullDomain, Storage::get('index.html')));
            fclose($fp);
        }
    }

    private function getTemplates()
    {
        $files = Storage::allFiles('templates');

        $out = [];

        foreach ($files as $id => $file) {
            $out[] = [$id + 1, str_replace('templates/', '', $file)];
        }

        return $out;
    }

    private function makeConfig()
    {
        $rawConfig = Storage::get('templates/' . $this->type);

        $rawConfig = str_replace("LAB_ROOT", env('WEBROOT') . $this->fullDomain . "/" . $this->appends, $rawConfig);
        $rawConfig = str_replace("LAB_SERVER_NAME", $this->fullDomain, $rawConfig);

        return $rawConfig;
    }
}
