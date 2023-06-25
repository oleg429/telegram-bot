<?php

namespace App\Telegram\Handlers;

use App\Telegram\Resolvers\TelegramMessageCartResolver;
use App\Telegram\Senders\DishCategorySender;
use Longman\TelegramBot\Entities\Message;

class DishCategoryMessageHandler
{
    /** @var TelegramMessageCartResolver */
    private $telegramMessageCartResolver;
    /** @var DishCategorySender */
    private $dishCategorySender;



    public function __construct(
        DishCategorySender $dishCategorySender,
        TelegramMessageCartResolver $telegramMessageCartResolver,
    )
    {
        $this->dishCategorySender = $dishCategorySender;
        $this->telegramMessageCartResolver = $telegramMessageCartResolver;
    }

    public function handle(Message $message)
    {
        $chatId = $message->getChat()->getId();
        $cart = $this->telegramMessageCartResolver->resolve($message);
        $companyId = $cart->getCompanyId();

        return $this->dishCategorySender->send($chatId, $companyId);
    }

}
