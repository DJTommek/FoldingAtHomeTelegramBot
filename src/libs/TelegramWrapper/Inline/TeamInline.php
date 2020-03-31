<?php

namespace TelegramWrapper\Inline;

use \Folding;
use \Icons;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;

class TeamInline extends Inline
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		if (isset($this->params[0])) {
			$foldingTeamId = htmlentities($this->params[0]);
		} else {
			$this->flash('Invalid button, parameter is not set.', true);
			return;
		}

		$stats = Folding::loadTeamStats($foldingTeamId);
		if (!$stats) {
			$this->flash(sprintf('%s Error: Team doesn\'t exists or Folding@home API is not available, try again later.', Icons::ERROR), true);
			return;
		}

		$text = Folding::formatTeamStats($stats, $foldingTeamId);

		$replyMarkup = new Markup();
		$replyMarkup->inline_keyboard = [
			[
				[
					'text' => Icons::REFRESH . ' Refresh',
					'callback_data' => '/team ' . $foldingTeamId,
				],
			],
		];
		if ($stats) {
			$replyMarkup->inline_keyboard[0][] = [
				'text' => Icons::DEFAULT . ' Set as default',
				'callback_data' => '/setteam ' . $stats->team . ' ' . $stats->name,
			];
		}

		$this->replyButton($text, $replyMarkup);
		$this->flash('Team stats were refreshed!');
	}
}