<?php

namespace App\Telegram\Senders;

use App\Services\Cart\DTO\CartDTO;
use App\Services\Cart\DTO\CartItemDTO;
use App\Telegram\Resolvers\TelegramMessageCartResolver;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Message;

class CartSender extends TelegramSender
{
    /** @var TelegramMessageCartResolver */
    private $telegramMessageCartResolver;

    public function __construct(
        TelegramMessageCartResolver $telegramMessageCartResolver,
    )
    {
        $this->telegramMessageCartResolver = $telegramMessageCartResolver;
    }

    public function send(Message $message){
        $chatId = $message->getFrom()->getId();

        $cart = $this->telegramMessageCartResolver->resolve($message);

        if($this->getCartInfo($cart)){
            $text = $this->getCartInfo($cart);
        } else {
            $text = trans('bots.cartEmpty');
        }

        $data = [
            'chat_id' => $chatId,
            'text'    => $text,
        ];
        return $this->sendData($data);
    }

    private function getCartInfo(CartDTO $cart): string
    {
        return $this->generateCarItemsMessage($cart);
    }

    /**
     * @param CartDTO $cart
     * @return string
     */
    private function generateCarItemsMessage(CartDTO $cart): string
    {
        $result = [];
        foreach ($cart->getItems() as $item) {
            $result[] = $this->generateCartItemMessage($item);
        }
        return implode(PHP_EOL, $result);
    }

    /**
     * @param array $item
     * @return string
     */
    private function generateCartItemMessage(CartItemDTO $item): string
    {
        return sprintf(
            '%s - %s грн',
            $item->getName(),
            $item->getPrice()
        );
    }


    public function getYesNoCompanyKeyboard(): InlineKeyboard
    {
        $items = [];
        $items[] = [[
                'text' => trans('bots.yes'),
                'callback_data' => '{"type": "changeCompany", "value": "yes"}',
            ],
            [
                'text' => trans('bots.no'),
                'callback_data' => '{"type": "changeCompany", "value": "no"}',
            ]];
        $keyboard = new InlineKeyboard(...$items);
        return $keyboard
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);
    }

    public function getYesNoCityKeyboard(): InlineKeyboard
    {
        $items = [];
        $items[] = [[
            'text' => 'Yes',
            'callback_data' => '{"type": "changeCity", "value": "yes"}',
        ],
            [
                'text' => 'No',
                'callback_data' => '{"type": "changeCity", "value": "no"}',
            ]];
        $keyboard = new InlineKeyboard(...$items);
        return $keyboard
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);
    }
}
