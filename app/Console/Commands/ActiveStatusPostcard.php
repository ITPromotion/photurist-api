<?php

namespace App\Console\Commands;

use App\Enums\ActionLocKey;
use App\Enums\MailingType;
use App\Enums\PostcardStatus;
use App\Models\Postcard;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActiveStatusPostcard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:active_status_postcard';

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
        $postcards = Postcard::where('status', PostcardStatus::LOADING)->where('draft',false)->get();

        Log::info(PostcardStatus::LOADING,$postcards);
        foreach ($postcards as $postcard){
            if($postcard->mediaContents()->where('loading', false)->get()->isEmpty()){

                $postcard->status = PostcardStatus::ACTIVE;

                $postcard->draft = false;

                if($postcard->status == MailingType::ACTIVE)
                    $postcard->start_mailing = Carbon::now();

                Log::info('loading');

                $postcard->save();

                try {
                    $user = $postcard->user;
                        (new NotificationService)->send([
                            'users' => $user->device->pluck('token')->toArray(),
                            'title' => $postcard->user->login,
                            'body' => __('notifications.postcard_status_active'),
                            'img' => $postcard->mediaContents[0]->link,
                            'postcard_id' => $postcard->id,
                            'action_loc_key' => ActionLocKey::GALLERY,
                            'badge' => DB::table('postcards_mailings')
                                ->where('view', 0)
                                ->where('user_id',Auth::id())
                                ->where('status', PostcardStatus::ACTIVE)
                                ->count()
                        ]);
                } catch (\Throwable $th) {
                    //throw $th;
                }

            };
        }

        return 0;
    }
}
