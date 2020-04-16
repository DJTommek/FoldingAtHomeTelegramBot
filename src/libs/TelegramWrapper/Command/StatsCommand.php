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
		parent::__construct($update, $tgLog, $loop);

		// @TODO should be dynamically loaded from parent class
		$command = '/stats';

		$exampleText = sprintf('%s DJTommek', $command) . PHP_EOL;
		$exampleText .= sprintf('%s 68256828', $command) . PHP_EOL;
		$exampleText .= sprintf('%s https://stats.foldingathome.org/donor/DJTommek', $command) . PHP_EOL;

		if (isset($this->params[0])) {
			// parameter is URL with donor ID
			if (mb_strpos($this->params[0], Folding::getUserUrl('')) === 0) {
				$foldingUserId = htmlentities(str_replace(Folding::getUserUrl(''), '', $this->params[0]));
			} else {
				// @TODO do some preg_match(), probably "^[^a-zA-Z0-9_%-]+$" (worked for top 100 on 2020-03-25)
				$foldingUserId = htmlentities($this->params[0]);
			}
			if (!$foldingUserId) {
				$this->reply(sprintf('%s <b>Error</b>: Parameter is not valid, it has to be nick, ID or valid URL. Examples: %s', Icons::ERROR, $exampleText));
				return;
			}
		} else {
			$foldingUserId = $user->getFoldingName();
			if (!$foldingUserId) {
				$msg = sprintf('%s <b>Error</b>: Missing required parameter nick, ID or URL. Examples:', Icons::ERROR) . PHP_EOL;
				$msg .= $exampleText;
				$msg .= PHP_EOL;
				$msg .= sprintf('%s PRO tip: load some user stats and then click on "%s Set as default". After that you can use %s command without parameters.', ICONS::INFO, ICONS::DEFAULT, $command) . PHP_EOL;
				$this->reply($msg);
				return;
			}
		}
		if (is_null($foldingUserId)) {
			$this->reply(sprintf('%s You have to set your nick first via /setNick &lt;nick or ID or URL&gt;', Icons::ERROR) . PHP_EOL);
			return;
		}
		$this->processStatsDonor($foldingUserId);
	}
}