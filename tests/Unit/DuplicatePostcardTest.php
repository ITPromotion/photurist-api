<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use App\Models\Postcard;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DuplicatePostcardTest extends TestCase
{
    use RefreshDatabase, MockeryPHPUnitIntegration,DatabaseMigrations;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testDuplicate() {
		m::mock('alias:App', [
			'make' => m::mock('Bkwld\Cloner\Cloner', [
				'duplicate' => m::mock('App\Models\Postcard'),
			])
		]);
        $postcard = new Postcard;
        $postcard->first();
	    $clone = $postcard->duplicate();
		$this->assertInstanceOf('App\Models\Postcard', $clone);
	}
}
