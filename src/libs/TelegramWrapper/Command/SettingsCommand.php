<?php

namespace TelegramWrapper\Command;

use \Folding;
use \Icons;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Button;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
use unreal4u\TelegramAPI\Telegram\Types\KeyboardButton;
use unreal4u\TelegramAPI\Telegram\Types\ReplyKeyboardMarkup;

class SettingsCommand extends Command
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop, $user);
		$replyMarkup = new Markup();
		$replyMarkup->inline_keyboard[] = [
			[
				'text' => sprintf('Settings'),
				'callback_data' => sprintf(self::CMD_SETTINGS),
			],
		];

		$this->reply('Just button here', $replyMarkup);
	}
}