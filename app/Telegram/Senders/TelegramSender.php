<?php

namespace App\Telegram\Senders;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class TelegramSender
{
    /**
     * @param array $data
     * @return ServerResponse|null
     */
    public function sendData(array $data): ?ServerResponse
    {
        try {
            return Request::sendMessage($data);
        } catch (TelegramException $e) {
            \Log::warning($e->getMessage(), $data);
        }
        return null;
    }
}
