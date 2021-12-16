<?php

namespace App\Services;

class NotificationService {


    public function send ($req = null) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $FcmToken = $req['users'];

        $serverKey = env('FIREBASE_SERVER_KEY','AAAAaaY_6k4:APA91bFE0tcfDXkfgeaawi6AtxsjIJj9t-iRCWOPYPglpZHvfWy4VhhMZ0x4lxVB5APqBaKkeQldDplXdUj825lmZFlHvO6qBsFEcfx3MGDONVP2bR7BEHct5xSl35EJ6J4UekzkEqBw');

        $data = [
            "registration_ids" => $FcmToken,
            "notification" => [
                "title" => $req['title'] ?? null,
                "body" => $req['body'] ?? null,
                'image' => 'https://dev.photurist.com/storage/'.$req['img'] ?? null,
                'sound' => 'default',
                // 'postcard_id' => $req['postcard_id'] ?? null,
            ],
            "data" => [
                'action_loc_key' => $req['action_loc_key'] ?? null,
                'postcard_id' => $req['postcard_id'] ?? null,
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
