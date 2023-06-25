<?php

namespace App\Telegram\Handlers;

use App\Services\Cart\Repositories\CartRepositoryInterface;
use App\Telegram\Resolvers\TelegramMessageCartResolver;
use App\Telegram\Senders\TelegramSender;
use Longman\TelegramBot\Entities\CallbackQuery;

class CompanyChangerHandler
{
    /** @var TelegramSender */
    private $telegramSender;
    /** @var CartRepositoryInterface */
    private $cartRepository;
    /** @var TelegramMessageCartResolver */
    private $telegramMessageCartResolver;

    public function __construct(
        TelegramSender              $telegramSender,
        CartRepositoryInterface     $cartRepository,
        TelegramMessageCartResolver $telegramMessageCartResolver,
    )
    {
        $this->telegramSender = $telegramSender;
        $this->cartRepository = $cartRepository;
        $this->telegramMessageCartResolver = $telegramMessageCartResolver;
    }

    public function handle(CallbackQuery $callbackQuery)
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $message = $callbackQuery->getMessage();
        $data = $callbackQuery->getData();
        $data_arr = json_decode($data, true);
        $dialog = $data_arr['value'];

        $cart = $this->telegramMessageCartResolver->resolve($message);

        if ($dialog == 'yes') {
            $this->cartRepository->clearItems($cart);
            $this->cartRepository->clearCompany($cart);
            $data = [
                'chat_id' => $chatId,
                'text' => trans('bots.changeCompanySuccessful'),
            ];
            return $this->telegramSender->sendData($data);
        }
        if ($dialog == 'no') {
            $data = [
                'chat_id' => $chatId,
                'text' => trans('bots.noChangeCompany'),
            ];
            return $this->telegramSender->sendData($data);
        }
    }
}
