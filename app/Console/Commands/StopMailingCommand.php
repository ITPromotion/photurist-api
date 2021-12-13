<?php

namespace App\Console\Commands;

use App\Enums\MailingType;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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

        $postcards->update(['status' => MailingType::CLOSED]);


        // try {
            // $postcardIds = $postcards->pluck('postcard_id')->toArray();

            // $userIds = DB::table('postcards')->whereIn('id', $postcardIds)->pluck('user_id')->toArray();
            // $users = DB::table('devices')->whereIn('user_id', $userIds)->pluck('token')->toArray();
            foreach ($postcards->get() as $postcard) {
                \Illuminate\Support\Facades\Log::info('Время рассылки истекло'.(new \App\Services\NotificationService)->send([
                    'users' => $postcard->user->device->pluck('token')->toArray(),
                    'title' => $postcard->user->login,
                    'body' => ActionLocKey::TIME_IS_UP_TEXT,
                    'img' => $postcard->mediaContents[0]->link,
                    'postcard_id' => $postcard->id,
                    'action_loc_key' => ActionLocKey::TIME_IS_UP,
                ]));
            }

            //
            foreach (Postcard::whereIn('id', $postcards->pluck('postcard_id')->toArray())->get() as $postcard) {
                $t = $postcards;
                $userPostcardNotificationsUsers = $postcard->userPostcardNotifications->pluck('id')->toArray();
                $postcardsUserId = DB::table('postcards_mailings')->where('postcard_id',$postcard->id)->whereNotIn('user_id', $userPostcardNotificationsUsers)->pluck('user_id')->toArray();
                // \Illuminate\Support\Facades\Log::info($postcardsUserId);
                // try {
                    \Illuminate\Support\Facades\Log::info('Время ожидание истекло'.(new \App\Services\NotificationService)->send([
                        'users' => \App\Models\Device::whereIn('user_id', $userPostcardNotificationsUsers)->pluck('token')->toArray(),
                        'title' => $postcard->user->login,
                        'body' => ActionLocKey::WAITING_TIME_TEXT,
                        'img' => $postcard->mediaContents[0]->link,
                        'postcard_id' => $postcard->id,
                        'action_loc_key' => ActionLocKey::WAITING_TIME,
                    ]));
                // } catch (\Throwable $th) {
                //     //throw $th;
                // }
            }

        // } catch (\Throwable $th) {
        //     //throw $th;
        // }
        return 0;
    }
}
