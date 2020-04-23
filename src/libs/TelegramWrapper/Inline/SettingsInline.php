<?php

namespace TelegramWrapper\Inline;

use \Folding;
use \Icons;
use TelegramWrapper\Command\Command;
use Tracy\Debugger;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;

class SettingsInline extends Inline
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop, $user);


		$userTimezone = $this->user->getTimezone();
		$text = sprintf('<b>Settings</b>') . PHP_EOL;
		$text .= sprintf('Choose one of the settings via buttons below:') . PHP_EOL;

		$replyMarkup = new Markup();
		$replyMarkup->inline_keyboard = [
			[ // row of buttons
				[ // button
					'text' => sprintf('%s Timezone: %s', Icons::CLOCK, $userTimezone->getName()),
					'callback_data' => sprintf(Command::CMD_SETTINGS_TIMEZONE),
				],
			],
		];

		$this->replyButton($text, $replyMarkup);
	}
}