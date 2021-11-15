<?php

namespace App\Services;

class NotificationService {


    public function send ($req = null) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $FcmToken = $req['users'];

        $serverKey = env('FIREBASE_SERVER_KEY',null);

        $data = [
            "registration_ids" => $FcmToken,
            "notification" => [
                "title" => $req['title'],
                "body" => $req['body'],
                'image' => 'https://dev.photurist.com/storage/'.$req['img'],
                'postcard_id' => $req['postcard_id'] ?? null,
                'action_loc_key' => $req['action_loc_key'] ?? null,

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
