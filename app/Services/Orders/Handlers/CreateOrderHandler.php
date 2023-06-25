<?php

namespace App\Services\Orders\Handlers;

use App\Models\Order;
use App\Services\Cart\DTO\CartDTO;
use App\Services\Dots\DotsService;
use App\Services\Dots\DTO\OrderDTO;
use App\Services\Orders\Repositories\OrderRepositoryInterface;
use App\Telegram\Senders\MessageSender;
use Longman\TelegramBot\Entities\Message;

class CreateOrderHandler
{

    /** @var DotsService */
    private $dotsService;
    /** @var OrderRepositoryInterface */
    private $orderRepository;
    /** @var \App\Telegram\Senders\MessageSender */
    private $messageSender;

    public function __construct(
        DotsService $dotsService,
        OrderRepositoryInterface $orderRepository,
        MessageSender $messageSender,
    ) {
        $this->dotsService = $dotsService;
        $this->orderRepository = $orderRepository;
        $this->messageSender = $messageSender;
    }

    /**
     * @param CartDTO $cartDTO
     * @return Order
     */
    public function handle(Message $message, CartDTO $cartDTO): ?Order
    {
        $orderResponse = $this->dotsService->makeOrder($this->generateDotsOrderData($cartDTO));


        if(!$this->orderIdCheck($message, $orderResponse)){
            return null;
        }

        return $this->orderRepository->createFromArray(
            $this->generateOrderData($cartDTO, $orderResponse)
        );
    }

    private function orderIdCheck(Message $message, array $orderResponse)
    {
        if(array_key_exists('id', $orderResponse)){
            return true;
        }
        if($orderResponse['message'] == 'The company does not work at the time selected in the order'){
            $this->messageSender->send($message->getChat()->getId(), trans('bots.wrongSchedule'));
            return false;
        }

        if($orderResponse['message'] == 'Incorrect payment type'){
            $this->messageSender->send($message->getChat()->getId(), trans('bots.wrongPayment'));
            return false;
        }
    }

    /**
     * @param CartDTO $cartDTO
     * @return array
     */
    private function generateDotsOrderData(CartDTO $cartDTO): array
    {
        $companyId = $cartDTO->getCompanyId();

        $companyAddresseId = $cartDTO->getAddressId();
        var_dump($companyAddresseId);
        return [
            'cityId' => $cartDTO->getCityId(),
            'companyId' => $companyId,
            'companyAddressId' => $companyAddresseId,
            'userName' => $cartDTO->getUser()->getName(),
            'userPhone' => $cartDTO->getUser()->getPhone(),
            'deliveryType' => OrderDTO::DELIVERY_PICKUP,
            'deliveryTime' => OrderDTO::DELIVERY_TIME_FASTEST,
            'paymentType' => OrderDTO::PAYMENT_ONLINE,
            'cartItems' => $this->generateDotsOrderCartData($cartDTO),
        ];
    }

    /**
     * @param CartDTO $cartDTO
     * @return array
     */
    private function generateDotsOrderCartData(CartDTO $cartDTO): array
    {
        $result = [];
        foreach ($cartDTO->getItems() as $item) {
            $result[] = [
                'id' => $item->getDishId(),
                'count' => $item->getCount(),
                'price' => $item->getPrice(),
            ];
        }
        return $result;
    }

    /**
     * @param CartDTO $cartDTO
     * @param array $dotsOrderData
     * @return array
     */
    private function generateOrderData(CartDTO $cartDTO, array $dotsOrderData): array
    {
        $data = $this->generateOrderDataFromCart($cartDTO);

        return $data;
    }

    /**
     * @param CartDTO $cartDTO
     * @return array
     */
    private function generateOrderDataFromCart(CartDTO $cartDTO): array
    {
        return [
            'userName' => $cartDTO->getUser()->getName(),
            'userPhone' => $cartDTO->getUser()->getPhone(),
            'user_id' => $cartDTO->getUser()->getId(),
            'items' => $cartDTO->getItemsArray(),
            'company_id' => $cartDTO->getCompanyId(),
        ];
    }

}
