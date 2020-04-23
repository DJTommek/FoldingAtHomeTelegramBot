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

		$userTimezone = $this->user->getTimezone();
		$text = sprintf('<b>Settings</b>') . PHP_EOL;
		$text .= sprintf('Choose one of the settings via buttons below:') . PHP_EOL;

		$replyMarkup = new Markup();
		$replyMarkup->inline_keyboard[] = [
			[
				'text' => sprintf('%s Timezone: %s', Icons::CLOCK, $userTimezone->getName()),
				'callback_data' => sprintf(Command::CMD_SETTINGS_TIMEZONE),
			],
		];

		$this->reply($text, $replyMarkup);
	}
}