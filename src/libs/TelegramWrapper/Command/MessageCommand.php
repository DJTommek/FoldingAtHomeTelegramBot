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
		$text .= sprintf('Check /help to get more info about bot.') . PHP_EOL;
		$this->reply($text);
	}

	private function runGroup() {
		// keep quiet in groups...
	}
}