<?php

namespace App\Telegram\Commands;

use App\Telegram\Handlers\CallbackQuery\CallbackQueryHandler;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class CallbackqueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Reply to callback query';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $response = app()->make(CallbackQueryHandler::class)->handle($this);
        $this->getCallbackQuery()->answer();
        return $response;
    }
}
