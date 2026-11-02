<?php

namespace App\Services;

use App\Contracts\PushSender;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\FirebaseProjectManager;
use Throwable;

class FirebasePushSender implements PushSender
{
    public function __construct(private FirebaseProjectManager $projects) {}

    public function send(array $tokens, string $title, string $body, string $url): array
    {
        if ($tokens === [] || ! $this->isConfigured()) {
            return [];
        }

        try {
            $messaging = $this->projects->project()->messaging();
            $message = CloudMessage::new()
                ->withNotification([
                    'title' => $title,
                    'body' => $body,
                ])
                ->withData([
                    'title' => $title,
                    'body' => $body,
                    'url' => $url,
                ])
                ->withWebPushConfig([
                    'headers' => ['Urgency' => 'high'],
                    'notification' => [
                        'icon' => url('/icons/icon-192.png'),
                        'badge' => url('/icons/icon-192.png'),
                    ],
                    'fcm_options' => ['link' => $url],
                ]);

            $invalidTokens = [];

            foreach (array_chunk($tokens, 500) as $tokenChunk) {
                $report = $messaging->sendMulticast($message, $tokenChunk);
                $invalidTokens = [
                    ...$invalidTokens,
                    ...$report->invalidTokens(),
                    ...$report->unknownTokens(),
                ];

                foreach ($report->failures()->getItems() as $failure) {
                    if ($failure->messageTargetWasInvalid() || $failure->messageWasSentToUnknownToken()) {
                        continue;
                    }

                    if ($error = $failure->error()) {
                        report($error);
                    }
                }
            }

            return array_values(array_unique($invalidTokens));
        } catch (Throwable $exception) {
            report($exception);

            return [];
        }
    }

    private function isConfigured(): bool
    {
        if (! config('services.firebase.push_enabled')) {
            return false;
        }

        $credentials = config('firebase.projects.app.credentials');

        if (! is_string($credentials) || $credentials === '') {
            return false;
        }

        $credentialsPath = str_starts_with($credentials, '/')
            ? $credentials
            : base_path($credentials);

        return is_readable($credentialsPath);
    }
}
