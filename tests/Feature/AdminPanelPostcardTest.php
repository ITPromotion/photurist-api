<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AdminPanelPostcardTest extends TestCase
{

    CONST PREFIX = '/api/v1/admin/';

    protected $user;

    public function singIn()
    {
        $this->user = Admin::first();
    }

    public function test_get_postcards()
    {
        $this->singIn();

        $url = self::PREFIX.'get-postcards';

        Passport::actingAs(
            $this->user,
            [$url],
            'api-admin'
        );

        $response = $this->get($url);

        $response->assertStatus(200);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
