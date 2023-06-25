<?php
namespace App\Telegram\Handlers\CallbackQuery;

use App\Services\Cart\CartService;
use App\Services\Dots\DotsService;
use App\Telegram\Resolvers\TelegramMessageCartResolver;
use App\Telegram\Senders\AddressSender;
use App\Telegram\Senders\CartSender;
use App\Telegram\Senders\TelegramSender;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Request;
use App\Services\Dots\Providers\DotsProvider;

class AddressHandler
{
    /** @var TelegramSender */
    private $telegramSender;
    /** @var AddressSender */
    private $addressSender;
    /** @var TelegramMessageCartResolver */
    private $telegramMessageCartResolver;
    /** @var CartService */
    private $cartService;
    /** @var CartSender */
    private $cartSender;

    /** @var DotsProvider */
    private $dotsProvider;
    /** @var DotsService */
    private $dotsService;

    public function __construct(
        AddressSender $addressSender,
        TelegramMessageCartResolver $telegramMessageCartResolver,
        CartService $cartService,
        CartSender $cartSender,
        TelegramSender $telegramSender,
        DotsProvider $dotsProvider,
        DotsService $dotsService
    )
    {
        $this->telegramSender = $telegramSender;
        $this->addressSender = $addressSender;
        $this->telegramMessageCartResolver = $telegramMessageCartResolver;
        $this->cartService = $cartService;
        $this->cartSender = $cartSender;
        $this->dotsProvider=$dotsProvider;
        $this->dotsService=$dotsService;
    }

    public function handle(CallbackQuery $callbackQuery)
    {
        $message = $callbackQuery->getMessage();
        $data = $callbackQuery->getData();
        $data_arr = json_decode($data, true);
        $companyId = $data_arr['id'];
        $chatId = $message->getChat()->getId();
        $cart = $this->telegramMessageCartResolver->resolve($message);


        if(array_key_exists('addresses', $this->dotsService->getCompanyInfo($companyId)) && !empty($this->dotsService->getCompanyInfo($companyId)['addresses'])) {
            $this->cartService->setCompanyId($cart, $companyId);
            return $this->addressSender->send($chatId, $companyId);
        }
        $cart->clearItems();
        $cart->clearCompany();
        $cart->clearCity();
        $cart->clearAddressId();
        $data = [
            'chat_id' => $chatId,
            'text' => 'There are no any addresses',
        ];

        return Request::sendMessage($data);
    }
}
