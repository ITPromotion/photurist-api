<?php

namespace Tests\Feature;

use App\Enums\PostcardStatus;
use App\Models\Postcard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserTest extends TestCase
{
    CONST PREFIX = '/api/v1/user/';

    protected $user;

    use WithFaker;

    public function singIn($login = null)
    {
        if(!$login) $login='unittest1';
        $this->user = User::where('login',$login)->first();
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_check_contacts()
    {
        $this->setUpFaker();

        $url = self::PREFIX.'check-contacts';

        $this->singIn();

        Passport::actingAs(
            $this->user,
            [$url]
        );

        $phones = [
            $this->faker->numerify('############'),
            $this->user->phone,
        ];

        $responseData = [
            'data' => [
                'users' => [
                        [
                            'id' => $this->user->id,
                            'phone' => $this->user->phone,
                        ],
                    ],
            ],
        ];


        $response = $this->putJson($url, ['phones' =>$phones]);

        $response
            ->assertStatus(200)
            ->assertJson($responseData) ;
    }

    public function test_add_contact_active()
    {
        $this->setUpFaker();

        $url = self::PREFIX.'add-contacts';

        $this->singIn();

        Passport::actingAs(
            $this->user,
            [$url]
        );

        $contact = User::where('login','unittest2')->select('phone','id')->first();


        $response = $this->postJson(
            $url, ['ids' =>[$contact->id]
        ]);

        $response
            ->assertStatus(200);
    }

    public function test_get_contacts_active()
    {
        $this->setUpFaker();

        $url = self::PREFIX.'get-contacts';

        $this->singIn();

        Passport::actingAs(
            $this->user,
            [$url]
        );

        $response = $this->getJson(
            $url, [
                'offset' =>0,
                'limit'=>10
            ]
        );

        $response
            ->assertStatus(200);
    }

    public function test_add_contact_block()
    {
        $this->setUpFaker();

        $url = self::PREFIX.'add-block-contacts';

        $this->singIn();

        Passport::actingAs(
            $this->user,
            [$url]
        );

        $client = User::where('login','unittest2')->select('phone','id')->first();

        $response = $this->postJson(
            $url, ['ids' =>[$client->id]
        ]);

        $response
            ->assertStatus(200);
    }

    public function test_get_contacts_block()
    {
        $this->setUpFaker();

        $url = self::PREFIX.'get-block-contacts';

        $this->singIn();

        Passport::actingAs(
            $this->user,
            [$url]
        );

        $response = $this->getJson(
            $url, [
                'offset' =>0,
                'limit'=>10
            ]
        );

        $response
            ->assertStatus(200);
    }

    public function test_add_contact_ignore()
    {
        $this->setUpFaker();

        $url = self::PREFIX.'add-ignore-contacts';

        $this->singIn();

        Passport::actingAs(
            $this->user,
            [$url]
        );

        $client = User::where('login','unittest2')->select('phone','id')->first();

        $response = $this->postJson(
            $url, ['ids' =>[$client->id]
        ]);

        $response
            ->assertStatus(200);
    }

    public function test_get_contacts_ignore()
    {
        $this->setUpFaker();

        $url = self::PREFIX.'get-ignore-contacts';

        $this->singIn();

        Passport::actingAs(
            $this->user,
            [$url]
        );

        $response = $this->getJson(
            $url, [
                'offset' =>0,
                'limit'=>10
            ]
        );

        $response
            ->assertStatus(200);
    }

    public function test_remove_contacts()
    {
        $this->setUpFaker();

        $url = self::PREFIX.'remove-contacts';

        $this->singIn();

        Passport::actingAs(
            $this->user,
            [$url]
        );

        $client = User::where('login','unittest2')->select('phone','id')->first();

        $response = $this->putJson(
            $url, ['ids' =>[$client->id]
        ]);

        $response
            ->assertStatus(200);
    }

    public function test_send_postcard_to_contact()
    {
        $this->setUpFaker();

        $url = self::PREFIX.'send-postcard-to-contact';

        $this->singIn('Miki');

        Passport::actingAs(
            $this->user,
            [$url]
        );

        $contact = User::where('login','Piter')->select('phone','id')->first();

        //if(!$contact) return 0;

        $postcard = $this->user->postcards()->first();

        dump($this->user);



        $response = $this->putJson(
            $url, [
                    'contact_id'    => $contact->id,
                    'postcard_id'   => $postcard->id,
                    ]);

        $response
            ->assertStatus(200);
    }
}
