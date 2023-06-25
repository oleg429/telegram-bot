<?php

namespace App\Telegram\Handlers\Commands;



use Longman\TelegramBot\Entities\Message;
use App\Models\Order;
use App\Services\Cart\CartService;
use App\Services\Dots\DotsService;
use App\Telegram\Resolvers\TelegramMessageCartResolver;
use App\Telegram\Senders\MessageSender;

class OrderCommandHandler
{
    /** @var TelegramMessageCartResolver */
    private $telegramMessageCartResolver;
    /** @var DotsService */
    private $dotsService;
    /** @var MessageSender */
    private $messageSender;
    /** @var CartService */
    private $cartService;


    public function __construct(
        TelegramMessageCartResolver $telegramMessageCartResolver,
        MessageSender $messageSender,
        CartService $cartService,
        DotsService $dotsService
    )
    {
        $this->telegramMessageCartResolver = $telegramMessageCartResolver;
        $this->dotsService = $dotsService;
        $this->messageSender = $messageSender;
        $this->cartService = $cartService;
    }

    public function handle(Message $message)
    {
        $cart = $this->telegramMessageCartResolver->resolve($message);

        if ($cart->getItems() == []) {
            return $this->sendEmptyCart($message);
        }

        $order = $this->cartService->createOrder($message, $cart);

        if(!$order){
            return $this->sendOrderFail($message);
        }

        return $this->sendSuccess($message);
    }

    /**
     * @param Message $message
     */
    private function sendEmptyCart(Message $message)
    {
        $text = trans('bots.cartEmpty');

        return $this->messageSender->send($message->getChat()->getId(), $text);
    }

    /**
     * @param Message $message
     */
    private function sendOrderFail(Message $message)
    {
        $text = trans('bots.orderFail');

        return $this->messageSender->send($message->getChat()->getId(), $text);
    }

    /**
     * @param Message $message
     * @param Order $order
     */
    private function sendSuccess(Message $message)
    {
        $text = trans('bots.orderSuccess');

        return $this->messageSender->send($message->getChat()->getId(), $text);
    }

}
