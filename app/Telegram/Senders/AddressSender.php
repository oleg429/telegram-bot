<?php

namespace App\Telegram\Senders;

use App\Services\Dots\DotsService;
use Longman\TelegramBot\Entities\InlineKeyboard;

class AddressSender extends TelegramSender
{
    /** @var DotsService */
    private $dotsService;

    public function __construct(
        DotsService $dotsService
    )
    {
        $this->dotsService = $dotsService;
    }

    public function send(int $chatId, string $companyId)
    {
        $inlineKeyboard = $this->getAddressKeyboard($companyId);
        if(!$inlineKeyboard){
            $data = [
                'chat_id' => $chatId,
                'text' => trans('bots.addressesNotFound'),
            ];
            return $this->sendData($data);
        }
        $data = [
            'chat_id' => $chatId,
            'text' => trans('bots.pleaseChooseAddRess'),
            'reply_markup' => $inlineKeyboard,
        ];
        return $this->sendData($data);
    }

    /**
     * @return InlineKeyboard
     */
    private function getAddressKeyboard(string $companyId): ?InlineKeyboard
    {
        $items = $this->getCompanyAddresses($companyId);
        if(!$items){
            return null;
        }
        $keyboard = new InlineKeyboard(...$items);
        return $keyboard
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);
    }

    /**
     * @return array
     */
    public function getCompanyAddresses(string $companyId): ?array
    {
        $company = $this->dotsService->getCompanyInfo($companyId);

        if(!array_key_exists('addresses', $company)){
            return null;
        }

        $addresses = [];
        foreach ($company['addresses'] as $address) {
            $addresses[] = [[
                'text' => $this->generateAddressText($address),
                'callback_data' => '{"type": "address", "id":"'. $address['id'] . '"}',
            ]];
        }
        return $addresses;
    }

    /**
     * @param array $company
     * @return string
     */
    private function generateAddressText(array $company): string
    {
            return $company['title'];
    }
}