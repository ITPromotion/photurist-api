<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserTest extends TestCase
{
    protected $user;

    public function singIn()
    {
        $this->user = User::first();
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_check_contacts()
    {
        $url = '/api/v1/user/check-contacts';

        $this->singIn();

        Passport::actingAs(
            $this->user,
            [$url]
        );

        $phones = [
            "380677188771",
            $this->user->phone,
        ];

        $responseData = [
            'data' => [
                'phones' => [
                    $this->user->phone,
                    ],
            ],
        ];


        $response = $this->putJson($url, ['phones' =>$phones]);

        $response
            ->assertStatus(200)
            ->assertJson($responseData) ;
    }
}
