<?php

namespace TelegramWrapper\Inline;

use \Folding;
use \Icons;
use Tracy\Debugger;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;

class SettingsInline extends Inline
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop, $user);

		$replyMarkup = new Markup();

		$userTimezone = $this->user->getSettings('timezone');
		$text = sprintf('<b>Settings</b>') . PHP_EOL;
		$text .= sprintf('Choose which settings via buttons below:') . PHP_EOL;

		$replyMarkup->inline_keyboard = [
			[ // row of buttons
				[ // button
					'text' => sprintf('Timezone: %s', $userTimezone),
					'callback_data' => sprintf('/settings-timezone'),
				],
			],
		];

		$this->replyButton($text, $replyMarkup);
		$this->flash('Just ACK flash');
	}
}