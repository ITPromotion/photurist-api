<?php

namespace App\Console\Commands;

use App\Enums\MailingType;
use App\Enums\PostcardStatus;
use App\Models\Postcard;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
        $postcards = Postcard::where('status', PostcardStatus::LOADING)->get();

        foreach ($postcards as $postcard){
            if($postcard->mediaContents()->where('loading', false)->get()->isEmpty()){

                $postcard->loading!=true;

                $postcard->status = PostcardStatus::ACTIVE;

                if($postcard->status == MailingType::ACTIVE)
                    $postcard->start_mailing = Carbon::now();

                $postcard->save();

            };
        }

        return 0;
    }
}
