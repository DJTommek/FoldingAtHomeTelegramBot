<?php

namespace TelegramWrapper\Inline;

use \Folding;
use \Icons;
use Tracy\Debugger;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
use unreal4u\TelegramAPI\Telegram\Types\ReplyKeyboardMarkup;

class SettingsTimezoneInline extends Inline
{
	/**
	 * SettingsTimezoneInline constructor.
	 *
	 * @param $update
	 * @param $tgLog
	 * @param $loop
	 * @param \User $user
	 * @throws \Exception
	 */
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
		// listing all timezone areas in specific continent
		if (isset($this->params[0])) {
			$replyMarkup = new ReplyKeyboardMarkup();
			$replyMarkup->one_time_keyboard = true;
			$replyMarkup->keyboard = $this->getTimezonesButtons($this->params[0]);
			$this->replyButton($text, $replyMarkup, false);
		} else {
			$replyMarkup->inline_keyboard = array_merge($replyMarkup->inline_keyboard, $this->getGroupsButtons());
			$this->replyButton($text, $replyMarkup);
		}
		$this->flash('Just ACK flash');
	}

	private function getGroupsButtons() {
		$inlineKeyboard = [];
		$buttonRow = [];

		$timezoneContinents = \Utils\Datetime::getTimezoneContinents();

		foreach ($timezoneContinents as $i => $timezoneContinent) {
			if ($i % 4 === 0) {
				$inlineKeyboard[] = $buttonRow;
				$buttonRow = [];
			}
			if (mb_strpos($this->user->getSettings('timezone'), $timezoneContinent) === 0) {
				$buttonText = sprintf('%s %s', Icons::CHECKED, $timezoneContinent);
			} else {
				$buttonText = $timezoneContinent;
			}
			$buttonRow[] = [
				'text' => $buttonText,
				'callback_data' => sprintf('/settings-timezone %s', $timezoneContinent),
			];
//			$this->reply(sprintf('Choose subzone from zone "%s": %s', $timezoneContinent, join(', ', \Utils\Datetime::getTZSubzone($timezoneContinent))));
		}
		if (count($buttonRow) > 0) {
			$inlineKeyboard[] = $buttonRow;
		}
		return $inlineKeyboard;
	}

	/**
	 * @param string $timezoneContinents
	 * @return array
	 * @throws \Exception
	 */
	private function getTimezonesButtons(string $timezoneContinents) {
		$inlineKeyboard = [];

		$timezoneAreas = \Utils\Datetime::getTimezonesByContinent($timezoneContinents);

		foreach ($timezoneAreas as $i => $timezoneArea) {
			$buttonText = $timezoneArea->getName();
			if ($this->user->getSettings('timezone') === $timezoneArea->getName()) {
				$buttonText .= ' ' . Icons::CHECKED;
			}
			$inlineKeyboard[] = [[
				'text' => $buttonText,
			]];
		}
		return $inlineKeyboard;
	}
}