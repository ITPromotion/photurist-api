<?php

namespace App\Console\Commands;

use App\Enums\ContactStatuses;
use App\Enums\MailingType;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Postcard;
use App\Enums\ActionLocKey;
use App\Jobs\NotificationJob;
use \App\Services\NotificationService;

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

        foreach ($postcards->get() as $postcard) {
            \Illuminate\Support\Facades\Log::info('waiting_time_text');

            $user = User::find($postcard->user_id);

            $postcard_ = Postcard::find($postcard->postcard_id);
            if ((!$postcard_->userPostcardNotifications()->where('user_id', $postcard->user_id)->first() && $postcard->user_id != $postcard_->user_id)&&
                //($user->contacts()->where('contact_id', $postcard_->user_id)->wherePivot('status', ContactStatuses::ACTIVE))->get()->isNotEmpty())
                (is_null($postcard_->additional_postcard_id)))

            try {
                $notification = [
                    'tokens' => User::find($postcard->user_id)->device->pluck('token')->toArray(),
                    'title' => $postcard_->user->login,
                    'body' => __('notifications.waiting_time_text'),
                    'img' => NotificationService::img($postcard_),
                    'action_loc_key' => ActionLocKey::WAITING_TIME,
                    'user_id' => $postcard->user_id,
                    'postcard_id' => $postcard->postcard_id,
                ];
                dispatch(new NotificationJob($notification));


            } catch (\Throwable $th) {

            }
        }
        $postcards->update(['status' => MailingType::CLOSED]);
        return 0;
    }
}
