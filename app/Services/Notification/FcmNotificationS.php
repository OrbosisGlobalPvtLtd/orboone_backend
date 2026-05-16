<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmNotificationS
{
    /**
     * Send FCM push notification.
     * Supports both HTTP v1 and Legacy API.
     */
    public function sendPush(
        string $token,
        string $title,
        string $body,
        array $data = []
    ): bool {
        if (empty($token)) {
            return false;
        }

        try {
            $projectId = env('FIREBASE_PROJECT_ID');
            $serverKey = env('FIREBASE_SERVER_KEY'); // Legacy fallback

            // If project ID is present, we try HTTP v1 (requires Access Token)
            if (!empty($projectId)) {
                return $this->sendHttpV1($projectId, $token, $title, $body, $data);
            }

            // Fallback to Legacy API
            if (!empty($serverKey)) {
                return $this->sendLegacy($serverKey, $token, $title, $body, $data);
            }

            Log::warning('FCM: No FIREBASE_PROJECT_ID or FIREBASE_SERVER_KEY found in .env');
            return false;
        } catch (\Exception $e) {
            Log::error('FCM Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send via HTTP v1 (requires OAuth2 Access Token).
     * Note: Requires google/apiclient or manual JWT signing.
     */
    private function sendHttpV1(string $projectId, string $token, string $title, string $body, array $data): bool
    {
        // Placeholder for Access Token. 
        // In a real scenario, use Google\Client to get this.
        // If not installed, this will fail gracefully.
        $accessToken = $this->getOAuthToken();

        if (!$accessToken) {
            Log::warning('FCM HTTP v1: Could not generate Access Token. Please check FIREBASE_CREDENTIALS or install google/apiclient.');
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $formattedData = [];
        foreach ($data as $key => $value) {
            $formattedData[(string)$key] = is_array($value) ? json_encode($value) : (string)$value;
        }

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $formattedData,
                'android' => [
                    'priority' => 'high',
                ],
            ],
        ];

        $response = Http::withToken($accessToken)->post($url, $payload);

        if ($response->failed()) {
            Log::error('FCM HTTP v1 Error: ' . $response->body());
            return false;
        }
        Log::info('FCM HTTP v1 Sent Successfully: ' . $response->body());
        return true;
    }

    /**
     * Send via Legacy FCM API (Simple Server Key).
     */
    private function sendLegacy(string $serverKey, string $token, string $title, string $body, array $data): bool
    {
        $url = "https://fcm.googleapis.com/fcm/send";

        $payload = [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => $data,
            'priority' => 'high',
        ];

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if ($response->failed()) {
            Log::error('FCM Legacy Error: ' . $response->body());
            return false;
        }

        return true;
    }

    /**
     * Generate OAuth2 Token for HTTP v1.
     */
    private function getOAuthToken(): ?string
    {
        $credentialsPath = env('FIREBASE_CREDENTIALS');

        if (! $credentialsPath) {
            Log::warning('FCM OAuth: FIREBASE_CREDENTIALS missing.');
            return null;
        }

        $fullPath = base_path($credentialsPath);

        if (! file_exists($fullPath)) {
            $fullPath = storage_path('app/firebase-service-account.json');
        }

        if (! file_exists($fullPath)) {
            Log::warning('FCM OAuth: credentials file not found at ' . $credentialsPath);
            return null;
        }

        if (class_exists('Google\Client')) {
            try {
                $client = new \Google\Client();
                $client->setAuthConfig($fullPath);
                $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

                $token = $client->fetchAccessTokenWithAssertion();

                if (! empty($token['error'])) {
                    Log::error('FCM OAuth token error: ' . json_encode($token));
                    return null;
                }

                return $token['access_token'] ?? null;
            } catch (\Exception $e) {
                Log::error('FCM OAuth Error: ' . $e->getMessage());
                return null;
            }
        }

        Log::warning('FCM OAuth: Google Client class not found.');
        return null;
    }
}
