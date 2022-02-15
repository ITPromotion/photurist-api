<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunBotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:run_bot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run bot from test';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Artisan::call('test', ['--filter' => 'test_create_postcard tests'.DIRECTORY_SEPARATOR.'Feature'.DIRECTORY_SEPARATOR.'PostcardTest.php']);

        return 0;
    }
}
