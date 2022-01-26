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
        $this->postcard = $notification['token'];
        $this->title = $notification['title'];
        $this->body = $notification['body'];
        $this->img = $notification['img'];
        $this->postcard_id = $notification['postcard_id'];
        $this->action_loc_key = $notification['action_loc_key'];
        $this->user_id = $notification['user_id'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new \App\Services\NotificationService)->send([
            'users' => $this->token,
            'title' => $this->title,
            'body' => $this->body,
            'img' => $this->img,
            'postcard_id' => $this->postcard_id,
            'action_loc_key' => $this->action_loc_key,
            'badge' => \Illuminate\Support\Facades\DB::table('postcards_mailings')
                                ->where('view', 0)
                                ->where('user_id',$this->user_id)
                                ->where('status', \App\Enums\PostcardStatus::ACTIVE)
                                ->count()
        ]);
    }
}
