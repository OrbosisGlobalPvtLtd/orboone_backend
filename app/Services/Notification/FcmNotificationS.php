<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FcmNotificationS
{
    private array $lastResponse = [];

    public function sendPush(
        string $token,
        string $title,
        string $body,
        array $data = []
    ): bool {
        if (empty($token)) {
            $this->lastResponse = ['success' => false, 'reason' => 'token_empty'];
            Log::warning('FCM send skipped: device token missing.');
            return false;
        }

        try {
            $projectId = (string) config('services.firebase.project_id');
            $serverKey = (string) config('services.firebase.server_key');

            if (!empty($projectId)) {
                return $this->sendHttpV1($projectId, $token, $title, $body, $data);
            }

            if (!empty($serverKey)) {
                return $this->sendLegacy($serverKey, $token, $title, $body, $data);
            }

            $this->lastResponse = ['success' => false, 'reason' => 'firebase_config_missing'];
            Log::warning('FCM: No FIREBASE_PROJECT_ID or FIREBASE_SERVER_KEY found in .env');
            return false;
        } catch (\Exception $e) {
            $this->lastResponse = [
                'success' => false,
                'reason' => 'exception',
                'message' => $e->getMessage(),
            ];

            Log::error('FCM Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function lastResponse(): array
    {
        return $this->lastResponse;
    }

    private function sendHttpV1(string $projectId, string $token, string $title, string $body, array $data): bool
    {
        $accessToken = $this->getOAuthToken();

        if (!$accessToken) {
            $this->lastResponse = ['success' => false, 'reason' => 'access_token_error'];
            Log::warning('FCM HTTP v1: Could not generate Access Token.');
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $imageUrl = $this->resolveImageUrl($data);
        $formattedData = $this->formatData($data, $title, $body, $imageUrl);

        // Send as a data-only payload for Android to run the custom client-side notification builder
        // which handles downloading private/protected attachments with the Bearer authentication token.
        // For iOS, the notification is triggered natively by including an APNs alert block.
        $payload = [
            'message' => [
                'token' => $token,
                'data' => $formattedData,
                'android' => [
                    'priority' => 'HIGH',
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $title,
                                'body' => $body,
                            ],
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        if ($response->failed()) {
            $bodyResponse = $response->json() ?: ['raw' => $response->body()];
            $status = data_get($bodyResponse, 'error.status');
            $message = data_get($bodyResponse, 'error.message');

            Log::error('FCM HTTP v1 Error', [
                'status_code' => $response->status(),
                'firebase_status' => $status,
                'message' => $message,
                'response' => $bodyResponse,
                'image_url' => $imageUrl,
            ]);

            $this->lastResponse = [
                'success' => false,
                'status_code' => $response->status(),
                'firebase_status' => $status,
                'message' => $message,
                'response' => $bodyResponse,
            ];

            if (in_array($status, ['UNREGISTERED', 'INVALID_ARGUMENT'], true)) {
                Log::warning('FCM HTTP v1 invalid device token', [
                    'token_prefix' => substr($token, 0, 16),
                    'firebase_status' => $status,
                ]);
            }

            if (in_array($response->status(), [401, 403], true) || in_array($status, ['UNAUTHENTICATED', 'PERMISSION_DENIED'], true)) {
                Cache::forget('firebase_http_v1_access_token');
                Log::error('FCM HTTP v1 unauthorized. OAuth token cache cleared.');
            }

            return false;
        }

        Log::info('FCM HTTP v1 sent successfully', [
            'response' => $response->json() ?: $response->body(),
            'image_url' => $imageUrl,
        ]);

        $this->lastResponse = [
            'success' => true,
            'status_code' => $response->status(),
            'response' => $response->json() ?: $response->body(),
            'image_url' => $imageUrl,
        ];

        return true;
    }

    private function sendLegacy(string $serverKey, string $token, string $title, string $body, array $data): bool
    {
        $url = "https://fcm.googleapis.com/fcm/send";

        $imageUrl = $this->resolveImageUrl($data);
        $formattedData = $this->formatData($data, $title, $body, $imageUrl);

        // Send as a data-only payload for Android to run the custom client-side notification builder
        $payload = [
            'to' => $token,
            'data' => $formattedData,
            'priority' => 'high',
        ];

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if ($response->failed()) {
            $this->lastResponse = [
                'success' => false,
                'status_code' => $response->status(),
                'response' => $response->json() ?: $response->body(),
            ];

            Log::error('FCM Legacy Error', [
                'status_code' => $response->status(),
                'response' => $response->json() ?: $response->body(),
                'image_url' => $imageUrl,
            ]);

            return false;
        }

        Log::info('FCM Legacy sent successfully', [
            'response' => $response->json() ?: $response->body(),
            'image_url' => $imageUrl,
        ]);

        $this->lastResponse = [
            'success' => true,
            'status_code' => $response->status(),
            'response' => $response->json() ?: $response->body(),
            'image_url' => $imageUrl,
        ];

        return true;
    }

    private function resolveImageUrl(array $data): ?string
    {
        $attachmentType = strtolower((string) ($data['attachment_type'] ?? ''));
        $imageUrl = $data['image_url'] ?? null;
        $attachmentUrl = $data['attachment_url'] ?? null;

        if (!$imageUrl && $attachmentType === 'image') {
            $imageUrl = $attachmentUrl;
        }

        if (!$imageUrl) {
            return null;
        }

        $imageUrl = trim((string) $imageUrl);

        if (!preg_match('/^https:\/\//i', $imageUrl)) {
            return null;
        }

        $path = parse_url($imageUrl, PHP_URL_PATH) ?: '';
        if (str_contains($path, '/api/v1/')) {
            return null;
        }

        return $imageUrl;
    }

    private function formatData(array $data, string $title, string $body, ?string $imageUrl): array
    {
        $data = array_merge($data, [
            'title' => $title,
            'body' => $body,
            'image_url' => $imageUrl ?? '',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ]);

        $formattedData = [];

        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $formattedData[(string) $key] = json_encode($value);
            } elseif (is_bool($value)) {
                $formattedData[(string) $key] = $value ? '1' : '0';
            } elseif (is_null($value)) {
                $formattedData[(string) $key] = '';
            } else {
                $formattedData[(string) $key] = (string) $value;
            }
        }

        return $formattedData;
    }

    private function getOAuthToken(): ?string
    {
        $credentialsPath = config('services.firebase.credentials_path');

        if (!$credentialsPath) {
            Log::warning('FCM OAuth: firebase credentials path missing.');
            return null;
        }

        $fullPath = $this->resolveCredentialsPath($credentialsPath);

        if (!file_exists($fullPath)) {
            Log::warning('FCM OAuth: credentials file not found at ' . $fullPath);
            return null;
        }

        return Cache::remember('firebase_http_v1_access_token', now()->addMinutes(50), function () use ($fullPath) {
            return $this->generateOAuthToken($fullPath);
        });
    }

    private function generateOAuthToken(string $fullPath): ?string
    {
        if (class_exists('Google\Client')) {
            try {
                $client = new \Google\Client();
                $client->setAuthConfig($fullPath);
                $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

                $token = $client->fetchAccessTokenWithAssertion();

                if (!empty($token['error'])) {
                    Log::error('FCM OAuth token error: ' . json_encode($token));
                    return null;
                }

                return $token['access_token'] ?? null;
            } catch (\Exception $e) {
                Log::error('FCM OAuth Error: ' . $e->getMessage());
                return null;
            }
        }

        return $this->generateOAuthTokenManually($fullPath);
    }

    private function generateOAuthTokenManually(string $fullPath): ?string
    {
        $credentials = json_decode((string) file_get_contents($fullPath), true);

        if (!is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            Log::error('FCM OAuth: invalid service account JSON.');
            return null;
        }

        $now = time();

        $jwtHeader = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ]));

        $jwtClaim = $this->base64UrlEncode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $unsignedJwt = $jwtHeader . '.' . $jwtClaim;
        $signature = '';

        if (!openssl_sign($unsignedJwt, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            Log::error('FCM OAuth: unable to sign JWT.');
            return null;
        }

        $jwt = $unsignedJwt . '.' . $this->base64UrlEncode($signature);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if ($response->failed()) {
            Log::error('FCM OAuth manual token error', [
                'status_code' => $response->status(),
                'response' => $response->json() ?: $response->body(),
            ]);
            return null;
        }

        return $response->json('access_token') ?: null;
    }

    private function resolveCredentialsPath(string $path): string
    {
        if (preg_match('/^[A-Za-z]:[\/\\\\]/', $path) || str_starts_with($path, '/')) {
            return $path;
        }

        return storage_path('app/private/firebase/firebase-service-account.json');
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
