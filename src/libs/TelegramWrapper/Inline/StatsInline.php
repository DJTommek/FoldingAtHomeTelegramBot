<?php

namespace TelegramWrapper\Inline;

use \Folding;
use \Icons;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Button;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
use unreal4u\TelegramAPI\Telegram\Types\KeyboardButton;
use unreal4u\TelegramAPI\Telegram\Types\ReplyKeyboardMarkup;

class StatsInline extends Inline
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		if (isset($this->params[0])) {
			$foldingUser = htmlentities($this->params[0]);
		} else {
			$this->flash('Invalid button, parameter is not set.', true);
			return;
		}

		$stats = Folding::loadUserStats($foldingUser);
		if (!$stats) {
			$this->flash(sprintf('%s Error: User doesn\'t exists or Folding@home API is not available, try again later.', Icons::ERROR), true);
		}

		$text = Folding::formatUserStats($stats, $foldingUser);

		$replyMarkup = new Markup();
		$replyMarkup->inline_keyboard = [
			[
				[
					'text' => Icons::REFRESH . ' Refresh',
					'callback_data' => '/stats ' . $foldingUser,
				],
			],
		];
		if ($stats) {
			[$foldingTeamId, $foldingTeamName] = Folding::getTeamDataFromUserStats($stats);
			$replyMarkup->inline_keyboard[0][] = [
				'text' => Icons::DEFAULT . ' Set as default',
				'callback_data' => '/setnick ' . $stats->id . ' ' . $stats->name . ' ' . $foldingTeamId . ' ' . $foldingTeamName,
			];
		}
		$this->replyButton($text, $replyMarkup);
		$this->flash('Refreshed!');
	}
}