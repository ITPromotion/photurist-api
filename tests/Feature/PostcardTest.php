<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Postcard;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PostcardTest extends TestCase
{

    CONST PREFIX = '/api/v1/user/';

    protected $user;

    use WithFaker;

    public function singIn()
    {
        $this->user = User::where('login','unittest1')->first();
    }

    public function test_duplicate()
    {
        $this->setUpFaker();
        $postcardId = Postcard::first()->id;
        $url = self::PREFIX.'duplicate/'.$postcardId;

        $this->singIn();

        Passport::actingAs(
            $this->user,
            [$url]
        );
        $response = $this->get($url);
        $response->assertStatus(200);
    }
}
