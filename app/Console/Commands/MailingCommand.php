<?php

namespace App\Console\Commands;

use App\Enums\MailingType;
use App\Enums\PostcardStatus;
use App\Models\Postcard;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use App\Enums\ActionLocKey;
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

            // if((!$lastMailing)||(Carbon::parse($lastMailing->start)->addMinutes(env('INTERVAL_STEP',5))<Carbon::now())){

                $userIds = DB::table('postcards_mailings')
                            ->where('postcard_id', $postcard->id)
                            ->pluck('user_id')
                            ->toArray();



                $usersOther = User::whereNotIn('id', $userIds)->get();

                // if($usersOther->isNotEmpty()) {
                    $user = User::find(8);
                    if ($user->id != $postcard->user_id) {

                        DB::table('postcards_mailings')->insert([
                            'user_id' => $user->id,
                            'postcard_id' => $postcard->id,
                            'status' => MailingType::ACTIVE,
                            'start' => Carbon::now(),
                            'stop' => Carbon::now()->addMinutes($postcard->interval_wait),
                        ]);

                        try {
                            if ($postcard->user_id != $user->id) {
                                (new NotificationService)->send([
                                    'users' => $user->device->pluck('token')->toArray(),
                                    'title' => $postcard->user->login,
                                    'body' => ActionLocKey::GALLERY_TEXT,
                                    'img' => $postcard->mediaContents[0]->link,
                                    'postcard_id' => $postcard->id,
                                    'action_loc_key' => ActionLocKey::GALLERY,
                                    'badge' => DB::table('postcards_mailings')
                                        ->where('view', 0)
                                        ->where('user_id',$user->id)
                                        ->where('status', PostcardStatus::ACTIVE)
                                        ->count()
                                ]);
                            }
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                    }

                // }
            // }

            $firstMailing = $postcard->firstMailing();

            if(($firstMailing)&&(Carbon::parse($firstMailing->start)<Carbon::now()->subMinutes($postcard->interval_send))){
                $postcard->status = PostcardStatus::ARCHIVE;
                $postcard->save();

                    \Illuminate\Support\Facades\Log::info('time_is_up_text');
                    (new \App\Services\NotificationService)->send([
                        'users' => $postcard->user->device->pluck('token')->toArray(),
                        'title' => $postcard->user->login,
                        'body' => __('notifications.time_is_up_text'),
                        'img' => count($postcard->mediaContents) ? $postcard->mediaContents[0]->link : null,
                        'postcard_id' => $postcard->id,
                        'action_loc_key' => ActionLocKey::TIME_IS_UP,
                        'badge' => \Illuminate\Support\Facades\DB::table('postcards_mailings')
                                    ->where('view', 0)
                                    ->where('user_id',$postcard->user->id)
                                    ->where('status', \App\Enums\PostcardStatus::ACTIVE)
                                    ->count()
                    ]);
            };
        }



        return 0;
    }
}
