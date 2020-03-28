<?php

namespace TelegramWrapper\Command;

use \Folding;
use \Icons;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Button;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
use unreal4u\TelegramAPI\Telegram\Types\KeyboardButton;
use unreal4u\TelegramAPI\Telegram\Types\ReplyKeyboardMarkup;

class StatsCommand extends Command
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		if (isset($this->params[0])) {
			// parameter is URL with donor
			if (mb_strpos($this->params[0], Folding::getUserUrl('')) === 0) {
				$foldingUser = htmlentities(str_replace(Folding::getUserUrl(''), '', $this->params[0]));
			} else {
				// @TODO do some preg_match(), probably "^[^a-zA-Z0-9_%-]+$" (worked for top 100 on 2020-03-25)
				$foldingUser = htmlentities($this->params[0]);
			}
		} else {
			$foldingUser = $user->getFoldingName();
		}
		if (is_null($foldingUser)) {
			$this->reply(sprintf('%s You have to set your nick first via /setNick &lt;nick or ID or URL&gt;', Icons::ERROR) . PHP_EOL);
			return;
		}
		if ($this->isButtonClick() === false) {
			$this->sendAction();
		}
		$stats = Folding::loadUserStats($foldingUser);
		$text = Folding::formatUserStats($stats, $foldingUser);

		dd($stats, false);

		$replyMarkup = new Markup();
		$replyMarkup->inline_keyboard = [
			[
				[
					'text' => Icons::REFRESH . ' Refresh',
					'callback_data' => '/stats' . ($stats ? ' ' . $stats->id : ''),
				],
			],
		];
		if ($stats) {
			$replyMarkup->inline_keyboard[0][] = [
				'text' => Icons::DEFAULT . ' Set as default',
				'callback_data' => '/setnick ' . $stats->id,
			];
		}
		if ($this->isButtonClick()) {
			$this->replyButton($text, $replyMarkup);
			$this->flash('Refreshed!');
		} else {
			$this->reply($text, $replyMarkup);
		}
	}
}