<?php

namespace TelegramWrapper\Command;

use \Folding;
use \Icons;

class StartCommand extends Command
{
	public function __construct($update, $tgLog, $loop) {
		parent::__construct($update, $tgLog, $loop);

		$message = sprintf('%s Welcome to %s!', Icons::FOLDING, TELEGRAM_BOT_NICK) . PHP_EOL;
		$message .= sprintf('Simple bot which help you to get statistics from <a href="%s">%s</a> website here into Telegram.', Folding::STATS_URL, Folding::STATS_URL) . PHP_EOL;
		$message .= sprintf('If you want to see your stats, use /stats or look into /help.') . PHP_EOL;
		$message .= PHP_EOL;
		$message .= sprintf('%s <b>Warning</b>: Website API is often very slow so be patient. Bot has automatic timeout set to %d seconds, then it will reply with sorry message.',
				Icons::WARNING, FOLDING_STATS_TIMEOUT) . PHP_EOL;

		$this->reply($message);
	}
}