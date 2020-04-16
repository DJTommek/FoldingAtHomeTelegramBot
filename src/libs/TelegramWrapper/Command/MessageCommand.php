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

	/**
	 * @throws \FoldingAtHome\Exceptions\GeneralException
	 */
	private function runPM() {
		if (mb_strpos($this->update->message->text, Folding::getUserUrl('')) === 0) {
			$foldingUserId = htmlentities(str_replace(Folding::getUserUrl(''), '', $this->update->message->text));
			$this->processStatsDonor($foldingUserId);
		} else if (mb_strpos($this->update->message->text, Folding::getTeamUrl('')) === 0) {
			$this->reply('loading team stats');
		} else {
			$text = sprintf('%s Welcome to %s!', Icons::FOLDING, TELEGRAM_BOT_NICK) . PHP_EOL;
			$text .= sprintf('Check /help to get more info about bot.') . PHP_EOL;
			$this->reply($text);
		}
	}

	private function runGroup() {
		// keep quiet in groups...
	}
}