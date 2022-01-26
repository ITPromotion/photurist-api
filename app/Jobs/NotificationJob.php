<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notification;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Illuminate\Support\Facades\Log::info($this->notification);
        (new \App\Services\NotificationService)->send([
            'users' => $this->notification['token'],
            'title' => $this->notification['title'],
            'body' => $this->notification['body'],
            'img' => $this->notification['img'],
            'postcard_id' => $this->notification['postcard_id'],
            'action_loc_key' => $this->notification['action_loc_key'],
            'badge' => \Illuminate\Support\Facades\DB::table('postcards_mailings')
                                ->where('view', 0)
                                ->where('user_id', $this->notification['user_id'])
                                ->where('status', \App\Enums\PostcardStatus::ACTIVE)
                                ->count()
        ]);

        return;
    }
}
