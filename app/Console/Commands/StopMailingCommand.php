<?php

namespace App\Console\Commands;

use App\Enums\MailingType;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StopMailingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:stop_mailing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        DB::table('postcards_mailings')
            ->where('status', MailingType::ACTIVE)
            ->where('stop','<', Carbon::now())
            ->update(['status' => MailingType::CLOSED]);
        return 0;
    }
}
