<?php

namespace App\Console\Commands;

use App\Enums\MailingType;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Postcard;
use App\Enums\ActionLocKey;

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
        $postcards = DB::table('postcards_mailings')
            ->where('status', MailingType::ACTIVE)
            ->where('stop','<', Carbon::now());

        // $postcards->update(['status' => MailingType::CLOSED]);


        // try {
            // $postcardIds = $postcards->pluck('postcard_id')->toArray();

            // $userIds = DB::table('postcards')->whereIn('id', $postcardIds)->pluck('user_id')->toArray();
            // $users = DB::table('devices')->whereIn('user_id', $userIds)->pluck('token')->toArray();
            // $userPostcardNotificationsUsers = $postcard->userPostcardNotifications->pluck('id')->toArray();
            // $postcardsUserId = DB::table('postcards_mailings')->where('postcard_id',$postcard->id)->whereNotIn('user_id', $userPostcardNotificationsUsers)->pluck('user_id')->toArray();
            foreach ($postcards->get() as $postcard) {
                \Illuminate\Support\Facades\Log::info('waiting_time_text');
                if (!Postcard::where($postcard->postcard_id)->first()->userPostcardNotifications()->where('user_id', $postcard->user_id)->first())
                (new \App\Services\NotificationService)->send([
                    'users' =>  User::find($postcard->user_id)->device->pluck('token')->toArray(),
                    'title' => User::find($postcard->user_id)->login,
                    'body' => __('notifications.waiting_time_text'),
                    'img' => Postcard::find($postcard->postcard_id)->first()->mediaContents[0]->link,
                    'postcard_id' => $postcard->postcard_id,
                    'action_loc_key' => ActionLocKey::WAITING_TIME,
                ]);
            }
        return 0;
    }
}
