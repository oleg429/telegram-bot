<?php

namespace App\Telegram\Resolvers;


use App\Services\Cart\CartService;
use App\Services\Cart\DTO\CartDTO;
use App\Services\Users\UsersService;
use App\Telegram\Generators\CartKeyGenerator;
use Longman\TelegramBot\Entities\Message;

class TelegramMessageCartResolver
{
    /** @var CartKeyGenerator */
    private $cartKeyGenerator;
    /** @var UsersService */
    private $usersService;
    /** @var CartService */
    private $cartService;

    public function __construct(
        CartKeyGenerator $cartKeyGenerator,
        UsersService $usersService,
        CartService $cartService
    )
    {
        $this->cartKeyGenerator = $cartKeyGenerator;
        $this->usersService = $usersService;
        $this->cartService = $cartService;
    }

    /**
     * @param Message $message
     * @return CartDTO
     */
    public function resolve(Message $message): CartDTO
    {
        $cartKey = $this->cartKeyGenerator->generate($message);
        $cartDTO = $this->cartService->getOrCreateCart($cartKey);
        $this->updateUserData($cartDTO, $message);

        return $cartDTO;
    }

    /**
     * @param CartDTO $cartDTO
     * @param Message $message
     */
    private function updateUserData(CartDTO $cartDTO, Message $message)
    {
        $user = $this->usersService->findUserByTelegramId($message->getChat()->getId());
        $cartUser = $cartDTO->getUser();
        if ($user) {
            $cartUser->setId($user->id);
            $cartUser->setName($user->name);
            $cartUser->setPhone($user->phone);
        } else {
            $cartUser->setName($message->getFrom()->getFirstName());
            if ($message->getContact()) {
                $cartUser->setPhone($message->getContact()->getPhoneNumber());
            }
        }
        $this->cartService->storeCart($cartDTO);
    }
}
