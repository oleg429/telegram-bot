<?php

namespace App\Telegram\Handlers\Commands;


use App\Telegram\Commands\Command;
use App\Telegram\Handlers\CallbackQuery\CompanyHandler;
use App\Telegram\Handlers\ClearCartHandler;
use App\Telegram\Handlers\DishCategoryMessageHandler;
use App\Telegram\Resolvers\MessageCommandResolver;
use App\Telegram\Senders\CartSender;
use App\Telegram\Senders\CitySender;
use App\Telegram\Senders\NotFoundMessageSender;
use Longman\TelegramBot\Entities\Message;

class GenericMessageCommandHandler
{
    /** @var OrderCommandHandler */
    private $orderCommandHandler;
    /** @var NotFoundMessageSender */
    private $notFoundMessageSender;
    /** @var MessageCommandResolver */
    private $messageCommandResolver;
    /** @var CitySender */
    private $citySender;
    /** @var CartSender */
    private $cartSender;

    public function __construct(
        OrderCommandHandler    $orderCommandHandler,
        NotFoundMessageSender  $notFoundMessageSender,
        MessageCommandResolver $messageCommandResolver,
        CitySender $citySender,
        CartSender $cartSender,
    )
    {
        $this->orderCommandHandler = $orderCommandHandler;
        $this->notFoundMessageSender = $notFoundMessageSender;
        $this->messageCommandResolver = $messageCommandResolver;
        $this->citySender = $citySender;
        $this->cartSender = $cartSender;
    }

    public function handle(Message $message)
    {

        $command = $this->messageCommandResolver->resolve($message);
        switch ($command) {
            case Command::ORDER:
                return $this->orderCommandHandler->handle($message);
            case Command::CITY:
                return $this->citySender->send($message->getFrom()->getId());
            case Command::COMPANY:
                return app(CompanyHandler::class)->handleMessage($message);
            case Command::CART:
                return $this->cartSender->send($message);
            case Command::DISHES:
                return app(DishCategoryMessageHandler::class)->handle($message);
            case Command::CLEAR:
                return app(ClearCartHandler::class)->handle($message);
            default:
                return $this->notFoundMessageSender->send($message->getChat()->getId());
        }
    }

}
