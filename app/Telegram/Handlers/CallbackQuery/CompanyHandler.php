<?php

namespace App\Telegram\Handlers\CallbackQuery;

use App\Services\Cart\CartService;
use App\Telegram\Resolvers\TelegramMessageCartResolver;
use App\Telegram\Senders\CartSender;
use App\Telegram\Senders\CompanySender;
use App\Telegram\Senders\TelegramSender;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use App\Services\Dots\Providers\DotsProvider;

class CompanyHandler
{
    /** @var TelegramSender */
    private $telegramSender;
    /** @var CompanySender */
    private $companySender;
    /** @var TelegramMessageCartResolver */
    private $telegramMessageCartResolver;
    /** @var CartService */
    private $cartService;
    /** @var CartSender */
    private $cartSender;

    /** @var DotsProvider */
    private $dotsProvider;

    public function __construct(
        CompanySender $companySender,
        TelegramMessageCartResolver $telegramMessageCartResolver,
        CartService $cartService,
        CartSender $cartSender,
        TelegramSender $telegramSender,
        DotsProvider $dotsProvider
    )
    {
        $this->telegramSender = $telegramSender;
        $this->companySender = $companySender;
        $this->telegramMessageCartResolver = $telegramMessageCartResolver;
        $this->cartService = $cartService;
        $this->cartSender = $cartSender;
        $this->dotsProvider=$dotsProvider;
    }

    public function handle(CallbackQuery $callbackQuery)
    {
        $message = $callbackQuery->getMessage();
        $data = $callbackQuery->getData();
        $data_arr = json_decode($data, true);
        $cityId = $data_arr['id'];
        $chatId = $message->getChat()->getId();

        $cart = $this->telegramMessageCartResolver->resolve($message);
        $cartCityId = $cart->getCityId();

        $data2 = [
            'chat_id' => $chatId,
            'text' => 'City: '.$this->dotsProvider->getCity($cityId)['name'],
        ];

        if($cartCityId != $cityId && $cart->getItems() != []){
            $Keyboard = $this->cartSender->getYesNoCompanyKeyboard();
            $data = [
                'chat_id' => $chatId,
                'text'    => trans('bots.requireChangeCity'),
                'reply_markup' => $Keyboard,
            ];
            $this->telegramSender->sendData($data2);
            return $this->telegramSender->sendData($data);
        }
        $this->cartService->setCityId($cart, $cityId);

        $this->telegramSender->sendData($data2);
        return $this->companySender->send($chatId, $cityId);
    }

    public function handleMessage(Message $message)
    {
        $cart = $this->telegramMessageCartResolver->resolve($message);

        $cityId = $cart->getCityId();
        $chatId = $message->getChat()->getId();

        return $this->companySender->send($chatId, $cityId);
    }
}
