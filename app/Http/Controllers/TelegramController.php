<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\TelegramHelper;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Log::info('Telegram Webhook:', $request->all());
        $update = $request->all();

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        if (isset($update['callback_query'])) {
            $this->callbackHandler($update['callback_query']);
        }

        return response()->json(['status' => 'ok']);
    }


    private function handleMessage($message)
    {
        $chatId = $message['chat']['id'];
        $messageId = $message['message_id'];
        $text = $message['text'] ?? '';

        match ($text) {
            "/start" => self::sendMenu($chatId, $messageId),
            default => TelegramHelper::sendMessageReply($chatId, "I didn't understand that. Please choose from the menu."),
        };
    }

    public static function sendMenu($chatId, $messageId = null)
    {
        $keyboard = [
            [
                ['text' => 'ℹ️ About Me', 'callback_data' => 'about'],
                ['text' => '📄 Download My CV', 'callback_data' => 'cv_options']
            ],
            [
                ['text' => '📩 Contact Me', 'callback_data' => 'contact'],
                ['text' => '⚙️ Settings', 'callback_data' => 'settings']
            ]
        ];

        TelegramHelper::sendMessage($chatId, "👋 Welcome! Choose an option:", $keyboard);
    }

    public static function callbackHandler($callbackQuery)
    {
        $callbackData = $callbackQuery['data'];
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];

        switch ($callbackData) {
            case 'cv_options':
                TelegramHelper::editMessage($chatId, $messageId, "Select the CV format:", [
                    [['text' => '📂 PDF', 'callback_data' => 'cv_pdf']],
                    [['text' => '📄 DOCX', 'callback_data' => 'cv_docx']],
                    [['text' => '🔗 View on LinkedIn', 'callback_data' => 'cv_linkedin']],
                    [['text' => '🔙 Back', 'callback_data' => 'back_to_menu']]
                ]);
                break;
            case 'about':
                TelegramHelper::editMessage($chatId, $messageId, "I am a Laravel developer. This bot showcases my portfolio!");
                TelegramHelper::editMessageKeyboard($chatId, $messageId, [
                    [['text' => '🔙 Back', 'callback_data' => 'back_to_menu']]
                ]);
                break;
            case 'contact':
                TelegramHelper::editMessage($chatId, $messageId, "📩 Contact me at: your.email@example.com", [
                    [['text' => '🔙 Back', 'callback_data' => 'back_to_menu']]
                ]);
                break;
            case 'settings':
                TelegramHelper::editMessage($chatId, $messageId, "⚙️ Settings menu coming soon!", [
                    [['text' => '🔙 Back', 'callback_data' => 'back_to_menu']]
                ]);
                break;
            case 'cv_pdf':
                TelegramHelper::editMessage($chatId, $messageId, "📂 Here is my CV in PDF format: [Download](https://example.com/cv.pdf)", [
                    [['text' => '🔙 Back', 'callback_data' => 'cv_options']]
                ]);
                break;
            case 'cv_docx':
                TelegramHelper::editMessage($chatId, $messageId, "📄 Here is my CV in DOCX format: [Download](https://example.com/cv.docx)", [
                    [['text' => '🔙 Back', 'callback_data' => 'cv_options']]
                ]);
                break;
            case 'cv_linkedin':
                TelegramHelper::editMessage($chatId, $messageId, "🔗 View my LinkedIn profile: [LinkedIn](https://linkedin.com/in/yourprofile)", [
                    [['text' => '🔙 Back', 'callback_data' => 'cv_options']]
                ]);
                break;
            case 'back_to_menu':
                TelegramHelper::editMessage($chatId, $messageId, "👋 Welcome! Choose an option:");
                TelegramHelper::editMessageKeyboard($chatId, $messageId, [
                    [
                        ['text' => 'ℹ️ About Me', 'callback_data' => 'about'],
                        ['text' => '📄 Download My CV', 'callback_data' => 'cv_options']
                    ],
                    [
                        ['text' => '📩 Contact Me', 'callback_data' => 'contact'],
                        ['text' => '⚙️ Settings', 'callback_data' => 'settings']
                    ]
                ]);
                break;
            default:
                TelegramHelper::sendMessage($chatId, "I didn't understand that request.");
                break;
        }
    }
}
