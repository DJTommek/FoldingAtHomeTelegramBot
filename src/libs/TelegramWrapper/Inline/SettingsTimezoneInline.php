<?php

namespace TelegramWrapper\Inline;

use \Folding;
use \Icons;
use Tracy\Debugger;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;

class SettingsTimezoneInline extends Inline
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop, $user);

		$replyMarkup = new Markup();

		$userTimezone = $this->user->getSettings('timezone');
		$text = sprintf('Currently set timezone: "%s"', $userTimezone) . PHP_EOL;

		$text .= sprintf('Choose new region by clicking on button') . PHP_EOL;
		$replyMarkup->inline_keyboard = [
			[
				[
					'text' => sprintf('%s Settings', Icons::BACK),
					'callback_data' => sprintf('/settings'),
				],
			]
		];
		if (isset($this->params[0])) {
			$replyMarkup->inline_keyboard[0][] = [
				'text' => sprintf('%s Timezone groups', Icons::BACK),
				'callback_data' => sprintf('/settings-timezone'),
			];

			$replyMarkup->inline_keyboard = array_merge($replyMarkup->inline_keyboard, $this->getTimezonesButtons($this->params[0]));
			$this->replyButton($text, $replyMarkup);
		} else {
			$replyMarkup->inline_keyboard = array_merge($replyMarkup->inline_keyboard, $this->getGroupsButtons());
			$this->replyButton($text, $replyMarkup);
		}
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
				'callback_data' => sprintf('/settings-timezone %s', $TZGroup),
			];
//			$this->reply(sprintf('Choose subzone from zone "%s": %s', $TZGroup, join(', ', \Utils\Datetime::getTZSubzone($TZGroup))));
		}
		if (count($buttonRow) > 0) {
			$inlineKeyboard[] = $buttonRow;
		}
		return $inlineKeyboard;
	}

	/**
	 * @param string $TZGroup
	 * @return array
	 * @throws \Exception
	 */
	private function getTimezonesButtons(string $TZGroup) {
		$inlineKeyboard = [];
		$buttonRow = [];

		$TZsubzones = \Utils\Datetime::getTZSubzone($TZGroup);

		foreach ($TZsubzones as $i => $TZsubzone) {
			if ($i % 2 === 0) {
				$inlineKeyboard[] = $buttonRow;
				$buttonRow = [];
			}
			if ($this->user->getSettings('timezone') === $TZsubzone) {
				$buttonText = sprintf('%s %s', Icons::CHECKED, $TZsubzone);
			} else {
				$buttonText = $TZsubzone;
			}
			$buttonRow[] = [
				'text' => $buttonText,
				'callback_data' => sprintf('/settings-timezone %s', $TZsubzone),
			];
//			$this->reply(sprintf('Choose subzone from zone "%s": %s', $TZGroup, join(', ', \Utils\Datetime::getTZSubzone($TZGroup))));
		}
		if (count($buttonRow) > 0) {
			$inlineKeyboard[] = $buttonRow;
		}
		return $inlineKeyboard;
	}
}