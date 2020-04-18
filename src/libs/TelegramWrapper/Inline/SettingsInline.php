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
		$text = sprintf('Currently set timezone: "%s"', $userTimezone) . PHP_EOL;

		$text .= sprintf('Choose new region by clicking on button') . PHP_EOL;
		$replyMarkup->inline_keyboard = [
			[
				'text' => sprintf('Settings'),
				'callback_data' => sprintf('/settings'),
			],
		];
		$replyMarkup->inline_keyboard = array_merge($replyMarkup->inline_keyboard, $this->getGroupsButtons());

		$this->replyButton($text, $replyMarkup);
		$this->flash('Just ACK flash');
	}

	private function getGroupsButtons() {
		$inlineKeyboard = [];
		$buttonRow = [];

		$TZGroups = \Utils\Datetime::getTZGroups();
		foreach ($TZGroups as $i => $TZGroup) {
			if ($i % 4 === 0) {
				$inlineKeyboard[] = $buttonRow;
				$buttonRow = [];
			}
			if (mb_strpos($this->user->getSettings('timezone'), $TZGroup) === 0) {
				$buttonText = sprintf('%s %s', Icons::CHECKED, $TZGroup);
			} else {
				$buttonText = $TZGroup;
			}
			$buttonRow[] = [
				'text' => $buttonText,
				'callback_data' => sprintf('/settings %s', $TZGroup),
			];
//			$this->reply(sprintf('Choose subzone from zone "%s": %s', $TZGroup, join(', ', \Utils\Datetime::getTZSubzone($TZGroup))));
		}
		if (count($buttonRow) > 0) {
			$inlineKeyboard[] = $buttonRow;
		}
		return $inlineKeyboard;
	}
}