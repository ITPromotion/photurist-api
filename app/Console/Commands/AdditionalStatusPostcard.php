<?php

namespace App\Console\Commands;

use App\Enums\ActionLocKey;
use App\Enums\PostcardStatus;
use App\Models\Postcard;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AdditionalStatusPostcard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

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

                /*try {
                    $user = $postcard->user;
                    (new NotificationService)->send([
                        'users' => $user->device->pluck('token')->toArray(),
                        'title' => $postcard->user->login,
                        'body' => __('notifications.postcard_status_draft'),
                        'img' => $postcard->mediaContents[0]->link,
                        'postcard_id' => $postcard->id,
                        'action_loc_key' => ActionLocKey::GALLERY_DRAFT,
                        'badge' => \Illuminate\Support\Facades\DB::table('postcards_mailings')
                            ->where('view', 0)
                            ->where('user_id',$user->id)
                            ->where('status', \App\Enums\PostcardStatus::ACTIVE)
                            ->count()
                    ]);
                } catch (\Throwable $th) {
                    //throw $th;
                }*/

            };
        }
        return 0;
    }
}
