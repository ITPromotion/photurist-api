<?php


namespace App\Services\AdminPanel;


use App\Models\Postcard;
use Illuminate\Support\Facades\DB;

class AdminPanelPostcardService
{
    private $postcard = null;

    public function __construct(Postcard $postcard)
    {
        $this->postcard = $postcard;
    }

    public function postcardInfo()
    {

        $this->postcard->author = $this->postcard->user->login;

        $this->postcard->user_send_count = DB::table('postcards_mailings')->where('postcard_id', $this->postcard->id)->count();

        $this->postcard->users_save_count = $this->postcard->users()->count();

        $this->postcard->users_not_save_count = $this->postcard->user_send_count - $this->postcard->users_save_count;

        $this->postcard->additionally_postcards_count = $this->postcard->additionally()->count();

        return $this->postcard;

    }

}