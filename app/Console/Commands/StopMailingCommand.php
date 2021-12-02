<?php

namespace App\Console\Commands;

use App\Enums\MailingType;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
            // ->where('status', MailingType::ACTIVE)
            ->where('stop','<', Carbon::now());

        $postcards->update(['status' => MailingType::CLOSED]);


        try {
            $postcardIds = $postcards->pluck('postcard_id')->toArray();

            $userIds = DB::table('postcards')->whereIn('id', $postcardIds)->pluck('user_id')->toArray();
            $users = DB::table('devices')->whereIn('user_id', $userIds)->pluck('token')->toArray();
            (new \App\Services\NotificationService)->send([
                'users' => $users,
                'title' => null,
                'body' => 'Время рассылки истекло, открытка больше не рассылается новым получателям',
                'img' => null,
                'postcard_id' => '',
                'action_loc_key' => ActionLocKey::TIME_IS_UP,
            ]);
            // foreach ($postcards->get() as $postcard) {
            //     \Illuminate\Support\Facades\Log::info($postcards->first());
            //     break;
            // }
            // foreach ($postcards->get() as $postcard) {
            //     \Illuminate\Support\Facades\Log::info($postcard->userPostcardNotifications);
            //     $postcard->userPostcardNotifications;
            //     try {
            //         (new \App\Services\NotificationService)->send([
            //             'users' => $postcard->users->device->pluck('token')->toArray(),
            //             'title' => $postcard->user->login,
            //             'body' => 'Время ожидание истекло',
            //             'img' => $postcard->mediaContents[0]->link,
            //             'postcard_id' => $postcard->id,
            //             'action_loc_key' => ActionLocKey::WAITING_TIME,
            //         ]);
            //     } catch (\Throwable $th) {
            //         //throw $th;
            //     }
            // }

        } catch (\Throwable $th) {
            //throw $th;
        }
        return 0;
    }
}
