<?php

namespace App\Services;
use App\Enums\MediaContentType;
use App\Models\Device;

class NotificationService {


    public static function img ($postcard) {
        $link = null;

        if (count ($postcard->mediaContents)) {
            if (MediaContentType::PHOTO == $postcard->mediaContents[0]->media_content_type) {
                return $link = $postcard->mediaContents[0]->large;
            } else {
                return $link = $postcard->mediaContents[0]->frame_large;
            }
        }
        return $link;
    }

    public static function getTokenUsers ($user_id) {
        return Device::where('user_id', $user_id)->pluck('token')->toArray();
    }

    public function send ($req = null) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $FcmToken = $req['users'];

        $serverKey = env('FIREBASE_SERVER_KEY','AAAAaaY_6k4:APA91bFE0tcfDXkfgeaawi6AtxsjIJj9t-iRCWOPYPglpZHvfWy4VhhMZ0x4lxVB5APqBaKkeQldDplXdUj825lmZFlHvO6qBsFEcfx3MGDONVP2bR7BEHct5xSl35EJ6J4UekzkEqBw');

        $data = [
            "registration_ids" => $FcmToken,
            "mutable_content" => true,
            "content_available" => true,
            "notification" => [
                "mutable_content" => true,
                "content_available" => true,
                "title" => $req['title'] ?? null,
                "body" => $req['body'] ?? null,
                'image' => 'https://dev.photurist.com/storage/'.$req['img'] ?? null,
                'sound' => 'default',
                'badge' => $req['badge'] ?? null,
                // 'postcard_id' => $req['postcard_id'] ?? null,
            ],
            "data" => [
                "mutable_content" => true,
                "content_available" => true,
                'action_loc_key' => $req['action_loc_key'] ?? null,
                'postcard_id' => $req['postcard_id'] ?? null,
                'media_type' => $req['media_type'] ?? null,
                'badge' => $req['badge'] ?? null,
            ],
        ];
        $encodedData = json_encode($data);

        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

        // Execute post
        $result = curl_exec($ch);

        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);

        // FCM response
        return($result);
    }

}
