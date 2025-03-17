<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramHelper
{
    private static $botToken;
    private static $telegramApiUrl;

    public static function init()
    {
        self::$botToken = config('services.telegram.bot_token');
        self::$telegramApiUrl = config('services.telegram.api_url') . self::$botToken;
    }

    public static function sendMessage($chatId, $text, $keyboard = null)
    {
        self::init();
        
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => $keyboard ? json_encode(['inline_keyboard' => $keyboard, 'resize_keyboard' => true]) : null,
        ];

        return self::callTelegramApi('/sendMessage', $data);
    }

    public static function editMessage($chatId, $text, $keyboard = null)
    {
        self::init();
        
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => $keyboard ? json_encode(['keyboard' => $keyboard, 'resize_keyboard' => true]) : null,
        ];

        return self::callTelegramApi('/sendMessage', $data);
    }

    public static function sendDocument($chatId, $filePath, $caption = '')
    {
        self::init();

        $data = [
            'chat_id' => $chatId,
            'caption' => $caption,
            'document' => curl_file_create($filePath),
        ];

        return self::callTelegramApi('/sendDocument', $data, true);
    }

    public static function callTelegramApi($endpoint, $data, $isMultipart = false)
    {
        self::init();
        
        $url = self::$telegramApiUrl . $endpoint;
        
        try {
            if ($isMultipart) {
                $response = Http::attach('document', file_get_contents($data['document']), basename($data['document']))
                    ->post($url, $data);
            } else {
                $response = Http::post($url, $data);
            }

            Log::info('Telegram API Response:', $response->json());
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Telegram API Error: ' . $e->getMessage());
            return null;
        }
    }
}
