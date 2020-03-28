<?php

namespace TelegramWrapper\Command;

use \Folding;
use \Icons;

class MessageCommand extends Command
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		if ($this->isPm()) {
			$this->runPM();
		} else {
			$this->runGroup();
		}
	}

	private function runPM() {
		$text = sprintf('%s Welcome to %s!', Icons::FOLDING, TELEGRAM_BOT_NICK) . PHP_EOL;
		$text .= sprintf('If you want to see your stats, use /stats or look into /help.') . PHP_EOL;
		$text .= PHP_EOL;
		$text .= sprintf('%s <b>Warning</b>: Website API is often very slow so be patient. Bot has automatic timeout set to %d seconds, then it will reply with sorry message.',
				Icons::WARNING, FOLDING_STATS_TIMEOUT) . PHP_EOL;
		$this->reply($text);
	}

	private function runGroup() {
		// keep quiet in groups...
	}
}