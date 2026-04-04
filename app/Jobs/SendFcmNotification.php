<?php

namespace App\Jobs;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendFcmNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $token, $data, $title, $message;

    public function __construct($token, $message, $title, $data)
    {
        $this->token = $token;
        $this->message = $message;
        $this->data = $data;
        $this->title = $title;
        Log::info("Dispatching job for token: {$token}");
    }

    public function handle()
    {
        $credentialsPath = storage_path(env('GOOGLE_APPLICATION_CREDENTIALS', 'app/firebase/transnet-a06df-fe2dcb6de1ad.json'));

        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $credentialsPath
        );

        $accessToken = $credentials->fetchAuthToken()['access_token'];

        // FCM V1
        $projectId = env('FCM_PROJECT_ID');

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $body = [
            "message" => [
                "token" => $this->token,
                "notification" => [
                    "title" => $this->title,
                    "body" => $this->message
                ],
                "data" => [
                     "notification" => json_encode($this->data, JSON_UNESCAPED_UNICODE),
                 ],
             
                 "android" => [
                     "priority" => "high"
                 ],
             
                 "apns" => [
                     "headers" => [
                         "apns-priority" => "10"
                     ]
                 ]
            ]
        ];

        $response = Http::withToken($accessToken)
            ->post($url, $body);

        Log::info("FCM response", [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        if ($response->status() == 400) {
           $json = $response->json();
            if (!empty($json['error']['details'][0]['errorCode']) 
                && $json['error']['details'][0]['errorCode'] === 'INVALID_ARGUMENT') {
                app(\App\Repositories\NotificationRepository::class)->delete_token($this->token);
                Log::info("Deleted invalid FCM token", ['token' => $this->token]);
            }
        }
    }
}
