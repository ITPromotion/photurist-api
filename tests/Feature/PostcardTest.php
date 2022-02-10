<?php

namespace Tests\Feature;

use App\Enums\MediaContentType;
use App\Enums\PostcardStatus;
use App\Enums\ResponseStatuses;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use SplFileInfo;
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

    public function singInBot()
    {
        $logins = [
            'Piter',
            'Miki',
            'Pamela',
            'Melane',
            'Mops',
            'Spark',
            'Lake',
            'Valentin',
            'Lemur',
            'Dunkan',

        ];

        $k = array_rand($logins);

        $this->user = User::where('login',$logins[$k])->first();

        dump($this->user);
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

    public function test_create_postcard()
    {
        // php artisan test --filter test_create_postcard tests/Feature/PostcardTest.php

        $cities = [
            'Berlin',
            'Prague',
            'Kyiv',
            'Vilnius',
            'London',
            'Munich',
            'Oslo',
            'Paris',
            'Rome',
            'Istanbul',
            'Vienna',
        ];

        $k = array_rand($cities);

        $videoExtension = ['mp4'];

        $photoExtension = ['jpg'];

        $directories = Storage::directories('public/bot');

        if(empty($directories)){
            return;
        }

        $directory = $directories[array_rand($directories)];

        $directoryName = basename(dirname($directory.'/index.htm'));

        $files = Storage::files($directory);

        $file = $files[array_rand($files)];

        $info = new SplFileInfo($file);

        $ext = $info->getExtension();

        $this->setUpFaker();

        $url = self::PREFIX.'postcard';

        $this->singInBot();

        Passport::actingAs(
            $this->user,
            [$url]
        );

        $response = $this->postJson($url);

        dump('create - ', $response->status());

        if($response->status() == ResponseStatuses::CREATED){

            $responseData = $response->decodeResponseJson();

            $url = self::PREFIX.'save-media';

            Passport::actingAs(
                $this->user,
                [$url]
            );

            $stub = storage_path('app/'.$file);
            $name = Str::uuid().'.'.$ext;
            $path = sys_get_temp_dir().'/'.$name;

            copy($stub, $path);

            $file = new UploadedFile($path, $name, filesize($path), null, false);

            $mediaContentType = null;

            if(in_array($ext, $videoExtension))
                $mediaContentType = MediaContentType::VIDEO;

            if(in_array($ext, $photoExtension))
                $mediaContentType = MediaContentType::PHOTO;

            $response = $this->postJson($url,
            [
                'postcard_id' => $responseData['data']['id'],
                'file' => $file,
                'media_content_type' => $mediaContentType,
            ]);
            dump('mediaContent - ', $response->status());
            if($response->status()==ResponseStatuses::CREATED){

                $responseDataMediaContent = $response->decodeResponseJson();
                $updateArray = [
                    'interval_send' => 1440,
                    'interval_wait' => 60,
                    'status' => PostcardStatus::ACTIVE,
                    'text_data' => [
                            [
                                'data' => $directoryName,
		                        'media_content_id'=> $responseDataMediaContent['data']['id'],
                            ],
                    ],
                    'geo_data' => [
                        [
                        'lat' => 54.7395,
                        'lng' => 24.7381,
                        'address' => $cities[$k],
                        'media_content_id' => null,
                            ],
                    ],
                    'tag_data' => [
                        $directoryName,
                    ]
                ];

                $url = self::PREFIX.'postcard-update/'.$responseData['data']['id'];

                $this->singIn();

                Passport::actingAs(
                    $this->user,
                    [$url]
                );

                $response = $this->putJson($url, $updateArray);
                dump('Active - ',$response->status());
            };
        };
    }
}
