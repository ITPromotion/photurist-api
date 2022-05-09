<?php

namespace App\Console\Commands;

use App\Enums\ActionLocKey;
use App\Enums\PostcardStatus;
use App\Models\Postcard;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\NotificationJob;

class AdditionalStatusPostcard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:additional_status_postcard';

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
        $postcards = Postcard::where('status', PostcardStatus::LOADING)->whereNotNull('additional_postcard_id')->get();

        Log::info($postcards);

        foreach ($postcards as $postcard){
            if($postcard->mediaContents()->where('loading', false)->get()->isEmpty()){

                $postcard->loading!=true;

                $postcard->status = PostcardStatus::ADDITIONAL;

                $postcard->loading=true;


                Log::info('Additional draft');

                $postcard->save();

                $user = $postcard->user;

                $userTokens = [];

                try {

                    if($postcard->additional_postcard_id){
                        $mainPostcard = Postcard::find($postcard->additional_postcard_id);
                        $mailingUserIds = DB::table('postcards_mailings')
                            ->where('postcard_id', $mainPostcard->id)
                            ->where('status', PostcardStatus::ACTIVE)->pluck('user_id');

                        Log::info(['mailingUserIds' => $mailingUserIds]);

                        if($mailingUserIds->isNotEmpty()) {
                            $mailingUserIds = $mailingUserIds->toArray();

                            foreach ($mailingUserIds as $mailingUserId) {
                                $mailingUser = User::find($mailingUserId);
                                if($mailingUser->device->pluck('token'))
                                    $userTokens = array_merge($userTokens, $mailingUser->device->pluck('token')->toArray());
                            }
                        }

                        if($mainPostcard->users)
                            foreach ($mainPostcard->users as $subscribeUser){
                                $userTokens = array_merge($userTokens, $subscribeUser->device->pluck('token')->toArray());
                            }

                        $userTokens = array_merge($userTokens, $mainPostcard->user->device->pluck('token')->toArray());

                    }

                    $actionLocKey = $postcard->additional_postcard_id?ActionLocKey::ADDITIONAL_POSTCARD:ActionLocKey::GALLERY;

                    Log::info(['token' => $userTokens]);

                    $notification = [
                        'tokens' => $userTokens,
                        'title' => $postcard->user->login,
                        'body' => __('notifications.postcard_status_active'),
                        'img' => NotificationService::img($postcard),
                        'action_loc_key' =>  $actionLocKey,
                        'user_id' => $user->id,
                        'postcard_id' => $postcard->id,
                        'main_postcard_id' => $postcard->additional_postcard_id,

                    ];
                    // (new NotificationService)->send([
                    //     'users' => $user->device->pluck('token')->toArray(),
                    //     'title' => $postcard->user->login,
                    //     'body' => __('notifications.additional_postcard'),
                    //     'img' => $postcard->mediaContents[0]->link,
                    //     'postcard_id' => $postcard->id,
                    //     'action_loc_key' => ActionLocKey::ADDITIONAL_POSTCARD,
                    //     'badge' => \Illuminate\Support\Facades\DB::table('postcards_mailings')
                    //         ->where('view', 0)
                    //         ->where('user_id',$user->id)
                    //         ->where('status', \App\Enums\PostcardStatus::ACTIVE)
                    //         ->count()
                    // ]);
                    dispatch(new NotificationJob($notification));

               } catch (\Throwable $th) {
                    //throw $th;
                    }

            };
        }
        return 0;
    }
}
