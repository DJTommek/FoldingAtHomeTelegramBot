<?php

namespace TelegramWrapper\Command;

use \Folding;
use \Icons;
use Tracy\Debugger;
use Utils\Datetime;

class MessageCommand extends Command
{
	/**
	 * MessageCommand constructor.
	 *
	 * @param $update
	 * @param $tgLog
	 * @param $loop
	 * @param \User $user
	 * @throws \FoldingAtHome\Exceptions\GeneralException
	 */
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop, $user);

		if ($this->isPm()) {
			$this->runPM();
		} else {
			$this->runGroup();
		}
	}

	/**
	 * @throws \FoldingAtHome\Exceptions\GeneralException
	 * @throws \Exception
	 */
	private function runPM() {
		if (mb_strpos($this->update->message->text, Folding::getDonorUrl('')) === 0) {
			$foldingUserId = htmlentities(str_replace(Folding::getDonorUrl(''), '', $this->update->message->text));
			$this->processStatsDonor($foldingUserId);
		} else if (mb_strpos($this->update->message->text, Folding::getTeamUrl('')) === 0) {
			$foldingTeamId = htmlentities(str_replace(Folding::getTeamUrl(''), '', $this->update->message->text));
			$this->processStatsTeam($foldingTeamId);
		} else {
			// timezone settings
			$textTimezone = explode(' ', $this->update->message->text)[0];
			foreach (Datetime::getTimezones() as $timezone) {
				if (mb_strtolower($textTimezone) === mb_strtolower($timezone->getName())) {
					$this->user->updateTimezone($timezone);
					$nowInUserTimezone = new \DateTime('now', $timezone);
					$this->reply(sprintf('%s Timezone was set to <b>%s</b>. Offset to UTC is <b>%s</b> so current datetime is <b>%s</b>.',
						Icons::CHECKED,
						$timezone->getName(),
						$nowInUserTimezone->format('P'),
						$nowInUserTimezone->format(DATETIME_FORMAT)
					)); // @TODO some nicer text, also add buttons
					return;
				}
			}
			$text = sprintf('%s Welcome to %s!', Icons::FOLDING, TELEGRAM_BOT_NICK) . PHP_EOL;
			$text .= sprintf('Check /help to get more info about bot.') . PHP_EOL;
			$this->reply($text);
		}
	}

	private function runGroup() {
		// keep quiet in groups...
	}
}