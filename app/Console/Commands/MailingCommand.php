<?php

namespace App\Console\Commands;

use App\Enums\MailingType;
use App\Enums\PostcardStatus;
use App\Enums\UserStatus;
use App\Models\Postcard;
use App\Models\User;
use App\Services\PostcardService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use App\Enums\ActionLocKey;
use App\Jobs\NotificationJob;
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

            $author = User::findOrFail($postcard->user_id);

            $lastMailing = $postcard->lastMailing();

            if((!$lastMailing)||(Carbon::parse($lastMailing->start)->addMinutes(env('INTERVAL_STEP',5))<Carbon::now())){

                $userIds = DB::table('postcards_mailings')
                            ->where('postcard_id', $postcard->id)
                            ->pluck('user_id')
                            ->toArray();



                $usersOther = User::whereNotIn('id', $userIds)->where('status', PostcardStatus::ACTIVE)->get();

                if($usersOther->isNotEmpty()) {
                    $user = $usersOther->random(1)->first();
                    if (($user->id != $postcard->user_id)&&
                        !$user->blockContacts->contains('id', $author->id)&&
                        ($user->status!=UserStatus::BLOCKED)&&
                        (!$postcard->sender_id)){

                        $postcardService = new PostcardService($postcard);

                        $postcardService->sendPostcard($user);

                    }

                }
            }

            $firstMailing = $postcard->start_mailing;
            if(($firstMailing)&&(Carbon::parse($firstMailing)<Carbon::now()->subMinutes($postcard->interval_send))){
                $postcard->status = PostcardStatus::ARCHIVE;
                $postcard->save();
                \Illuminate\Support\Facades\Log::info('time_is_up_text');

                    try {
                        if(!$postcard->additional_postcard_id){

                        $notification = [
                            'tokens' => $postcard->user->device->pluck('token')->toArray(),
                            'title' => $postcard->user->login,
                            'body' => __('notifications.time_is_up_text'),
                            'img' => NotificationService::img($postcard),
                            'action_loc_key' => ActionLocKey::GALLERY,
                            'user_id' => $postcard->user_id,
                            'postcard_id' => $postcard->id,
                            'main_postcard_id' => $postcard->additional_postcard_id,
                        ];
                        dispatch(new NotificationJob($notification));
                        }

                        \Illuminate\Support\Facades\Log::info('time_is_up_text');
                        // (new \App\Services\NotificationService)->send([
                        //     'users' => $postcard->user->device->pluck('token')->toArray(),
                        //     'title' => $postcard->user->login,
                        //     'body' => __('notifications.time_is_up_text'),
                        //     'img' => count($postcard->mediaContents) ? $postcard->mediaContents[0]->link : null,
                        //     'postcard_id' => $postcard->id,
                        //     'action_loc_key' => ActionLocKey::TIME_IS_UP,
                        //     'badge' => \Illuminate\Support\Facades\DB::table('postcards_mailings')
                        //                 ->where('view', 0)
                        //                 ->where('user_id',$postcard->user->id)
                        //                 ->where('status', \App\Enums\PostcardStatus::ACTIVE)
                        //                 ->count()
                        // ]);
                    } catch (\Throwable $th) {
                        //throw $th;
                    }

            };
        }



        return 0;
    }
}
