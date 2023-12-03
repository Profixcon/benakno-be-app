<?php

namespace App\Traits;

trait GlobalTrait
{
    public function sendToDiscord($message)
    {
        // Place your code here to send a message to Discord
        // Example code using Discord Webhooks
        $webhookUrl = env('DISCORD_WEBHOOK_URL', '');
        if (!$webhookUrl) {
            // Handle the case when the webhook URL is not set in the .env file
            return false;
        }

        $timestamp = date("c", strtotime("now"));
        $msg = json_encode([
            "username" => "Benakno BOT",

            "tts" => false,

            "embeds" => [
                [
                    // Title
                    "title" => "Benakno Logger",

                    // Embed Type, do not change.
                    "type" => "rich",

                    // Description
                    "description" => $message ?? "No message provided",

                    // Timestamp, only ISO8601
                    "timestamp" => $timestamp,

                    // Left border color, in HEX
                    "color" => hexdec("3366ff"),
                ]
            ]

        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        // If you need to debug, or find out why you can't send message uncomment line below, and execute script.
        curl_close($ch);

        return $response;
    }
}
