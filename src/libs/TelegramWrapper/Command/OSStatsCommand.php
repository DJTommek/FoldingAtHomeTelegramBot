<?php

namespace TelegramWrapper\Command;

use Folding;
use FoldingAtHome\Exceptions\GeneralException;
use Icons;

class OSStatsCommand extends Command
{
	/**
	 * OSStatsCommand constructor.
	 *
	 * @param $update
	 * @param $tgLog
	 * @param $loop
	 * @param \User $user
	 */
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop, $user);

		$this->processStatsOS();
	}
}