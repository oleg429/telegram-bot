<?php

namespace App\Telegram\Handlers\Commands;

use App\Services\Users\UsersService;
use App\Telegram\Senders\CitySender;
use App\Telegram\Senders\MenuSender;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class ContactCommandHandler extends StartCommandHandler
{
    /** @var UsersService */
    private $usersService;
    /** @var MenuSender */
    private $telegramMenuSender;

    public function __construct(
        UsersService $usersService,
        CitySender   $citySender,
        MenuSender   $telegramMenuSender,
    )
    {
        $this->usersService = $usersService;
        $this->citySender = $citySender;
        $this->telegramMenuSender = $telegramMenuSender;
    }

    public function handle(SystemCommand $systemCommand): ?ServerResponse
    {
        $message = $systemCommand->getMessage();
        $telegramUserId = $message->getFrom()->getId();
        $user = $this->usersService->findUserByTelegramId($telegramUserId);
        if ($user && $message->getContact()) {
            $phoneNumber = $message->getContact()->getPhoneNumber();
            if ($phoneNumber[0] === '+'){
                $phoneNumber = substr($phoneNumber,1);
            }
            $this->usersService->updateUser($user, [
                'phone' => $phoneNumber,
            ]);
        }
        return $this->telegramMenuSender->send($user->telegram_id);
    }
}
