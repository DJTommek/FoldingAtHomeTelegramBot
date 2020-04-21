<?php

namespace TelegramWrapper\Command;

use Folding;
use FoldingAtHome\Exceptions\GeneralException;
use Icons;

class DonorCommand extends Command
{
	/**
	 * DonorCommand constructor.
	 *
	 * @param $update
	 * @param $tgLog
	 * @param $loop
	 * @param \User $user
	 * @throws GeneralException
	 */
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop, $user);

		// @TODO should be dynamically loaded from parent class

		$exampleText = sprintf('%s DJTommek', self::CMD_DONOR) . PHP_EOL;
		$exampleText .= sprintf('%s 68256828', self::CMD_DONOR) . PHP_EOL;
		$exampleText .= sprintf('%s %s', self::CMD_DONOR, Folding::getDonorUrl('DJTommek')) . PHP_EOL;

		if (isset($this->params[0])) {
			// parameter is URL with donor ID
			if (mb_strpos($this->params[0], Folding::getDonorUrl('')) === 0) {
				$foldingDonorId = htmlentities(str_replace(Folding::getDonorUrl(''), '', $this->params[0]));
			} else {
				// @TODO do some preg_match(), probably "^[^a-zA-Z0-9_%-]+$" (worked for top 100 on 2020-03-25)
				$foldingDonorId = htmlentities($this->params[0]);
			}
			if (!$foldingDonorId) {
				$this->reply(sprintf('%s <b>Error</b>: Parameter is not valid, it has to be nick, ID or valid URL. Examples: %s %s', Icons::ERROR, PHP_EOL, $exampleText));
				return;
			}
		} else {
			$foldingDonorId = $user->getFoldingName();
			if (!$foldingDonorId) {
				$msg = sprintf('%s <b>Error</b>: Missing required parameter nick, ID or URL. Examples:', Icons::ERROR) . PHP_EOL;
				$msg .= $exampleText;
				$msg .= PHP_EOL;
				$msg .= sprintf('%s PRO tip: load some donor\'s stats and then click on "%s Set as default". After that you can use "%s" command without parameters.', ICONS::INFO, ICONS::DEFAULT, self::CMD_DONOR) . PHP_EOL;
				$this->reply($msg);
				return;
			}
		}
		$this->processStatsDonor($foldingDonorId);
	}
}