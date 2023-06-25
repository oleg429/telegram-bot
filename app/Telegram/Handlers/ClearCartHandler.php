<?php

namespace App\Telegram\Handlers;

use App\Services\Cart\CartService;
use App\Telegram\Resolvers\TelegramMessageCartResolver;
use App\Telegram\Senders\TelegramSender;
use Longman\TelegramBot\Entities\Message;

class ClearCartHandler
{

    /** @var TelegramMessageCartResolver */
    private $telegramMessageCartResolver;
    /** @var CartService */
    private $cartService;

    /** @var TelegramSender */
    private $telegramSender;


    public function __construct(
        TelegramMessageCartResolver $telegramMessageCartResolver,
        CartService $cartService,
        TelegramSender $telegramSender
    )
    {
        $this->telegramMessageCartResolver = $telegramMessageCartResolver;
        $this->cartService = $cartService;
        $this->telegramSender=$telegramSender;
    }

    public function handle(Message $message)
    {
        $cart = $this->telegramMessageCartResolver->resolve($message);
        $chatId = $message->getChat()->getId();
        $this->cartService->clearItems($cart);
        $data = [
            'chat_id' => $chatId,
            'text' => trans('bots.cartCleared'),
        ];
        return $this->telegramSender->sendData($data);
    }

}
