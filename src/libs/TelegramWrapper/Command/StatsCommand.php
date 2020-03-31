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

		// @TODO should be dynamically loaded from parent class
		$command = '/stats';

		$exampleText = sprintf('%s DJTommek', $command) . PHP_EOL;
		$exampleText .= sprintf('%s 68256828', $command) . PHP_EOL;
		$exampleText .= sprintf('%s https://stats.foldingathome.org/donor/DJTommek', $command) . PHP_EOL;

		if (isset($this->params[0])) {
			// parameter is URL with donor ID
			if (mb_strpos($this->params[0], Folding::getUserUrl('')) === 0) {
				$foldingUser = htmlentities(str_replace(Folding::getUserUrl(''), '', $this->params[0]));
			} else {
				// @TODO do some preg_match(), probably "^[^a-zA-Z0-9_%-]+$" (worked for top 100 on 2020-03-25)
				$foldingUser = htmlentities($this->params[0]);
			}
			if (!$foldingUser) {
				$this->reply(sprintf('%s <b>Error</b>: Parameter is not valid, it has to be nick, ID or valid URL. Examples: %s', Icons::ERROR, $exampleText));
				return;
			}
		} else {
			$foldingUser = $user->getFoldingName();
			if (!$foldingUser) {
				$msg = sprintf('%s <b>Error</b>: Missing required parameter nick, ID or URL. Examples:', Icons::ERROR) . PHP_EOL;
				$msg .= $exampleText;
				$msg .= PHP_EOL;
				$msg .= sprintf('Pro tip: load some user stats and then click on "%s Set as default". After that you can use /stats command without parameters.', ICONS::DEFAULT) . PHP_EOL;
				$this->reply($msg);
				return;
			}
		}
		if (is_null($foldingUser)) {
			$this->reply(sprintf('%s You have to set your nick first via /setNick &lt;nick or ID or URL&gt;', Icons::ERROR) . PHP_EOL);
			return;
		}
		$this->sendAction();
		$stats = Folding::loadUserStats($foldingUser);
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
			$replyMarkup->inline_keyboard[0][] = [
				'text' => Icons::DEFAULT . ' Set as default',
				'callback_data' => '/setnick ' . $stats->name . ' ' . $stats->id,
			];
		}
		$this->reply($text, $replyMarkup);
	}
}