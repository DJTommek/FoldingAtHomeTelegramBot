<?php

namespace TelegramWrapper\Command;

use Folding;
use FoldingAtHome\Exceptions\GeneralException;
use Icons;

class StatsCommand extends Command
{
	/**
	 * StatsCommand constructor.
	 *
	 * @param $update
	 * @param $tgLog
	 * @param $loop
	 * @param \User $user
	 * @throws GeneralException
	 */
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop, $user);
		$this->reply(sprintf('%s To load donor\'s stats, use command %s',  Icons::INFO, Command::CMD_DONOR));
	}
}