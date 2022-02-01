<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    CONST PREFIX = '/api/v1/admin/';

    protected $user;

    use WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function singIn()
    {
        $this->user = Admin::first();
    }

    public function test_checkMobile () {
        $admin = Admin::first();
        $url = "/api/v1/check-mobile?phone=$admin->phone&admin_panel=true";
        $response = $this->getJson(
            $url
        );

        $response
            ->assertStatus(201);
    }

    public function test_checkOtp () {
        $admin = Admin::first();
        $url = "/api/v1/check-mobile?phone=$admin->phone&admin_panel=true";
        $response = $this->getJson(
            $url
        );
        $codeOTP = $response->original['codeOTP'];
        $url = "/api/v1/admin-check-otp";

        $response = $this->postJson(
            $url, ['phone_number' => $admin->phone, 'code_otp' => $codeOTP]
        );
        $response
            ->assertStatus(200);
    }

    public function test_getRole () {
        $this->setUpFaker();

        $url = self::PREFIX.'get-role';

        $this->singIn();
        Passport::actingAs(
            $this->user,
            [$url],
            'api-admin'

        );
        $response = $this->get($url);

        $response->assertStatus(200);
    }

    public function test_getPersonnel () {
        $this->setUpFaker();

        $url = self::PREFIX.'get-personnel';

        $this->singIn();
       Passport::actingAs(
            $this->user,
            [$url],
            'api-admin'

        );
        $response = $this->get($url);

        $response->assertStatus(200);
    }

    public function test_getPermissions () {
        $this->setUpFaker();

        $url = self::PREFIX.'get-permissions';

        $this->singIn();
       Passport::actingAs(
            $this->user,
            [$url],
            'api-admin'

        );
        $response = $this->get($url);

        $response->assertStatus(200);
    }

    public function test_createPersonnel () {
        $this->setUpFaker();

        $url = self::PREFIX.'create-personnel';

        $this->singIn();
       Passport::actingAs(
            $this->user,
            [$url],
            'api-admin'

        );
        $response = $this->postJson($url, [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'role_id' => 1,
        ]);

        $response->assertStatus(201);
    }

    public function test_createRole () {
        $this->setUpFaker();

        $url = self::PREFIX.'create-role';

        $this->singIn();
       Passport::actingAs(
            $this->user,
            [$url],
            'api-admin'

        );
        $response = $this->postJson($url, [
            'name' => $this->faker->title(),
        ]);

        $response->assertStatus(201);
    }

}
