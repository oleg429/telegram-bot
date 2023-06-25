<?php

namespace App\Telegram\Handlers;

use App\Services\Cart\CartService;
use App\Services\Dots\DotsService;
use App\Telegram\Resolvers\TelegramMessageCartResolver;
use App\Telegram\Senders\TelegramSender;
use Longman\TelegramBot\Entities\CallbackQuery;

class ItemHandler
{

    /** @var TelegramMessageCartResolver */
    private $telegramMessageCartResolver;
    /** @var CartService */
    private $cartService;
    /** @var DotsService */
    private $dotsService;

    /** @var TelegramSender */
    private $telegramSender;
    public function __construct(
        TelegramMessageCartResolver $telegramMessageCartResolver,
        CartService $cartService,
        DotsService $dotsService,
        TelegramSender $telegramSender,
    )
    {
        $this->telegramMessageCartResolver = $telegramMessageCartResolver;
        $this->cartService = $cartService;
        $this->dotsService = $dotsService;
        $this->telegramSender = $telegramSender;
    }

    public function handle(CallbackQuery $callbackQuery)
    {
        $message = $callbackQuery->getMessage();
        $cart = $this->telegramMessageCartResolver->resolve($message);
        $data = $callbackQuery->getData();
        $data_arr = json_decode($data, true);
        $companyId = $cart->getCompanyId();
        $chatId = $message->getChat()->getId();

        $dish = $this->dotsService->findDishById($data_arr['id'], $companyId);

        if(!$dish){
            $data = [
                'chat_id' => $chatId,
                'text'    => trans('bots.dishNotFound'),
            ];
            return $this->telegramSender->sendData($data);
        }

        $this->cartService->addItem($cart, [
            'dish_id' => $dish['id'],
            'name' => $dish['name'],
            'price' => $dish['price'],
        ]);

        $data = [
            'chat_id' => $chatId,
            'text'    => trans('bots.dishAddedToCart'),
        ];
        return $this->telegramSender->sendData($data);
    }

}
