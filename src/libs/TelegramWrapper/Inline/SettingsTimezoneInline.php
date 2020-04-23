<?php

namespace TelegramWrapper\Inline;

use \Folding;
use \Icons;
use TelegramWrapper\Command\Command;
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

		$userTimezone = $this->user->getTimezone();
		$text = sprintf('Currently set timezone %s', $this->formatTimezone($userTimezone, true)) . PHP_EOL;

		$replyMarkup->inline_keyboard = [
			[
				[
					'text' => sprintf('%s Settings', Icons::BACK),
					'callback_data' => sprintf(Command::CMD_SETTINGS),
				],
			]
		];
		// listing all timezone areas in specific continent
		if (isset($this->params[0])) {
			if ($this->params[0] === 'UTC') {
				// @TODO special treatment if timezone is UTC, because it is only one in that continent group
			}
			$replyMarkup = new ReplyKeyboardMarkup();
			$replyMarkup->one_time_keyboard = true;
			$replyMarkup->keyboard = $this->getTimezonesButtons($this->params[0]);
			// @TODO add button to request location and detect timezone by that
			$text .= sprintf('Choose new timezone by selecting one below. All are in format:') . PHP_EOL;
			$text .= sprintf('"Some/Timezone UTC offset %s current time"', Icons::CLOCK) . PHP_EOL;
			$this->replyButton($text, $replyMarkup, false);
		} else {
			$text .= sprintf('Choose your region by clicking on button:') . PHP_EOL;
			$replyMarkup->inline_keyboard = array_merge($replyMarkup->inline_keyboard, $this->getContinentsButtons());
			$this->replyButton($text, $replyMarkup);
		}
	}

	private function formatTimezone(\DateTimeZone $timezone, bool $checked = false) {
		$nowInTimezone = new \DateTime('now', $timezone);
		return sprintf('%s%s UTC%s %s %s',
			$timezone->getName(),
			($checked ? ' ' . Icons::CHECKED : ''), // show icon only if $checked = true, otherwise show nothing.
			$nowInTimezone->format('P'),
			Icons::CLOCK,
			$nowInTimezone->format(TIME_FORMAT)
		);
	}

	private function getContinentsButtons() {
		$inlineKeyboard = [];
		$buttonRow = [];

		$timezoneContinents = \Utils\Datetime::getTimezoneContinents();

		foreach ($timezoneContinents as $i => $timezoneContinent) {
			if ($i % 4 === 0) {
				$inlineKeyboard[] = $buttonRow;
				$buttonRow = [];
			}
			if (mb_strpos($this->user->getSettings('timezone')->getName(), $timezoneContinent) === 0) {
				$buttonText = sprintf('%s %s', Icons::CHECKED, $timezoneContinent);
			} else {
				$buttonText = $timezoneContinent;
			}
			$buttonRow[] = [
				'text' => $buttonText,
				'callback_data' => sprintf('%s %s', Command::CMD_SETTINGS_TIMEZONE, $timezoneContinent),
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
		$timezones = \Utils\Datetime::getTimezonesByContinent($timezoneContinents);
		foreach ($timezones as $i => $timezoneArea) {
			$inlineKeyboard[] = [[
				'text' => $this->formatTimezone($timezoneArea, $this->user->getTimezone()->getName() === $timezoneArea->getName()),
			]];
		}
		return $inlineKeyboard;
	}
}