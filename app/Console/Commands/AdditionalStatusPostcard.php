<?php

namespace App\Console\Commands;

use App\Enums\ActionLocKey;
use App\Enums\PostcardStatus;
use App\Models\Postcard;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
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

                $additionallyTokens = [];

              //  try {

                    if($postcard->additional_postcard_id){

                        $mainPostcard = Postcard::find($postcard->additional_postcard_id);

                        Log::info($mainPostcard);
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
                                $additionallyTokens = array_merge($additionallyTokens, $subscribeUser->device->pluck('token')->toArray());
                            }

                        $additionallyTokens = array_merge($additionallyTokens, $mainPostcard->user->device->pluck('token')->toArray());

                    }

                    $actionLocKey = $postcard->additional_postcard_id?ActionLocKey::ADDITIONAL_POSTCARD:ActionLocKey::GALLERY;

                    DB::table('postcards_mailings')
                        ->where('postcard_id', $mainPostcard->id)
                        ->where('status', PostcardStatus::ACTIVE)
                        ->update(['start' => Carbon::now()]);

                    $mainPostcard->updated_at = Carbon::now();

                    $mainPostcard->save();

                    Log::info(['tokens' => $userTokens]);

                    $notification = [
                        'tokens' => $userTokens,
                        'title' => $postcard->user->login,
                        'body' => __('notifications.postcard_status_active'),
                        'img' => NotificationService::img($postcard),
                        'action_loc_key' =>  $actionLocKey,
                        'user_id' => $user->id,
                        'postcard_id' => $postcard->id,
                        'main_postcard_id' => $postcard->additional_postcard_id,
                        'additionally_count' => null,
                    ];
                    dispatch(new NotificationJob($notification));

                    if($postcard->additional_postcard_id) {
                        $notification = [
                            'tokens' => $additionallyTokens,
                            'title' => $postcard->user->login,
                            'body' => __('notifications.postcard_additionally_status_active'),
                            'img' => NotificationService::img($postcard),
                            'action_loc_key' => $actionLocKey,
                            'user_id' => $user->id,
                            'postcard_id' => $postcard->id,
                            'main_postcard_id' => $postcard->additional_postcard_id,
                            'additionally_count' => null,
                        ];
                        dispatch(new NotificationJob($notification));
                    }

/*               } catch (\Throwable $th) {
                    //throw $th;
                    }*/

            };
        }
        return 0;
    }
}
