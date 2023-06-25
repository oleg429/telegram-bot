<?php

namespace App\Telegram\Handlers\Commands;

use App\Telegram\Handlers\CreateTelegramUserHandler;
use App\Telegram\Senders\RequestPhoneSender;
use App\Telegram\Senders\MenuSender;
use Longman\TelegramBot\Commands\SystemCommand;

class StartCommandHandler
{
    /** @var CreateTelegramUserHandler  */
    private $createTelegramUserHandler;

    /** @var MenuSender */
    private $telegramMenuSender;
    /** @var RequestPhoneSender */
    private $requestPhoneSender;

    public function __construct(
        CreateTelegramUserHandler $createTelegramUserHandler,

        MenuSender                $telegramMenuSender,
        RequestPhoneSender        $requestPhoneSender
    )
    {
        $this->createTelegramUserHandler = $createTelegramUserHandler;

        $this->telegramMenuSender = $telegramMenuSender;
        $this->requestPhoneSender = $requestPhoneSender;
    }

    public function handle(SystemCommand $systemCommand)
    {
        $user = $this->createTelegramUserHandler->handle($systemCommand->getMessage());
        if (!$user->phone) {
            return $this->requestPhoneSender->send($user->telegram_id);
        }
        return $this->telegramMenuSender->send($user->telegram_id);
    }
}
