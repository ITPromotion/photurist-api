<?php

namespace App\Console\Commands;

use App\Enums\MailingType;
use App\Enums\PostcardStatus;
use App\Models\Postcard;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MailingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:mailing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command mailing';

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

        $postcards = Postcard::where('status',PostcardStatus::ACTIVE)->get();

        foreach($postcards as $postcard){

            $lastMailing = $postcard->lastMailing();

            if((!$lastMailing)||(Carbon::parse($lastMailing->start)>Carbon::now()->subMinutes($postcard->interval_step))){

                $userIds = DB::table('postcards_mailings')
                            ->where('postcard_id', $postcard->id)
                            ->pluck('user_id')
                            ->toArray();



                $usersOther = User::whereNotIn('id', $userIds)->get();

                if($usersOther->isNotEmpty()) {
                    $user = $usersOther->random(1)->first();

                    DB::table('postcards_mailings')->insert([
                        'user_id' => $user->id,
                        'postcard_id' => $postcard->id,
                        'status' => MailingType::ACTIVE,
                        'start' => Carbon::now(),
                        'stop' => Carbon::now()->addMinutes($postcard->interval_send),
                    ]);
                }
            }
        }



        return 0;
    }
}
