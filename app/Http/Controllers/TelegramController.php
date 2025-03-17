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
            $chatId = $update['message']['chat']['id'];
            $text = $update['message']['text'];

            match ($text) {
                "/start" => TelegramHelper::sendMessage($chatId, "👋 Welcome! Choose an option:",[
                    [
                        ['text' => '📄 Download My CV', 'callback_data' => 'setup_group'],
                        ['text' => 'ℹ️ About Me', 'callback_data' => 'setup_private']
                    ],
                    [
                        ['text' => '📩 Contact Me', 'callback_data' => 'about'],
                        ['text' => '⚙️ Settings', 'callback_data' => 'help']
                    ]
                ]),
                "📄 Download My CV" => TelegramHelper::sendMessage($chatId, "Select the format:", [
                    [['text' => '📂 PDF'], ['text' => '📄 DOCX']],
                    [['text' => '🔗 View on LinkedIn']],
                ]),
                default => TelegramHelper::sendMessage($chatId, "I didn't understand that. Please choose from the menu.", null),
            };
        }

        return response()->json(['status' => 'ok']);
    }
}
