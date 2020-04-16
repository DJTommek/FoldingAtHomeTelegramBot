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
	/**
	 * SettingsCommand constructor.
	 *
	 * @param $update
	 * @param $tgLog
	 * @param $loop
	 * @param \User $user
	 * @throws \Exception
	 */
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop, $user);

		$text = sprintf('Currently set timezone: "%s"', $this->user->getSettings('timezone')) . PHP_EOL;
		$TZGroups = \Utils\Datetime::getTZGroups();
		$text .= sprintf('Choose area: %s', join(', ', $TZGroups)) . PHP_EOL;
		foreach ($TZGroups as $TZGroup) {
			$text .= sprintf('Choose subzone from zone "%s": %s', $TZGroup, join(', ', \Utils\Datetime::getTZSubzone($TZGroup))) . PHP_EOL;
		}
		$this->reply($text);
	}
}