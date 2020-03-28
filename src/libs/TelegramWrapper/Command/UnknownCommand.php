<?php

namespace TelegramWrapper\Command;

use \Folding;
use \Icons;

class UnknownCommand extends Command
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		$text = sprintf('%s Sorry, I don\'t know command...', Icons::ERROR) . PHP_EOL; // @TODO add info which command was written
		$text .= sprintf('Try /help to get list of all commands.');
		$this->reply($text);
	}
}