<?php

namespace TelegramWrapper\Command;

use \Folding;
use FoldingAtHome\Exceptions\ApiErrorException;
use FoldingAtHome\Exceptions\ApiTimeoutException;
use FoldingAtHome\Exceptions\GeneralException;
use FoldingAtHome\Exceptions\NotFoundException;
use FoldingAtHome\RequestTeam;
use FoldingAtHome\RequestUser;
use \Icons;
use Tracy\Debugger;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;

class TeamCommand extends Command
{
	/**
	 * TeamCommand constructor.
	 *
	 * @param $update
	 * @param $tgLog
	 * @param $loop
	 * @param \User $user
	 * @throws GeneralException
	 */
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop, $user);

		$command = '/team';

		$exampleText = sprintf('%s 239186', $command) . PHP_EOL;
		$exampleText .= sprintf('%s https://stats.foldingathome.org/team/239186', $command) . PHP_EOL;

		if (isset($this->params[0])) {
			// parameter is URL with donor
			if (mb_strpos($this->params[0], Folding::getTeamUrl('')) === 0) {
				$param = str_replace(Folding::getTeamUrl(''), '', $this->params[0]);
			} else {
				$param = $this->params[0];
			}
			if (is_numeric($param)) {
				$foldingTeamId = intval($param);
			} else {
				$this->reply(sprintf('%s <b>Error</b>: Parameter is not valid, it has to be Team ID or valid URL. Examples: %s', Icons::ERROR, PHP_EOL . $exampleText));
				return;
			}
		} else {
			$foldingTeamId = $user->getFoldingTeamId();
		}
		$this->processStatsTeam($foldingTeamId);
	}
}