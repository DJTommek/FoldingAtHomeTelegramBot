<?php

namespace TelegramWrapper\Inline;

use \Folding;
use \Icons;

class TeamCommand extends Command
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		$foldingTeamId = null;
		if (isset($this->params[0])) {
			// parameter is URL with donor
			if (mb_strpos($this->params[0], Folding::getTeamUrl('')) === 0) {
				$foldingTeamIdParam = str_replace(Folding::getTeamUrl(''), '', $this->params[0]);
				if (is_numeric($foldingTeamIdParam)) {
					$foldingTeamId = $foldingTeamIdParam;
				}
			} else {
				if (is_numeric($this->params[0])) {
					$foldingTeamId = $this->params[0];
				}
			}
		} else {
			$foldingTeamId = $user->getFoldingTeamId();
		}
		if (is_null($foldingTeamId)) {
			$this->reply(sprintf('%s You have to set your team first via /setTeam &lt;ID or URL&gt;', Icons::ERROR) . PHP_EOL);
			return;
		}

		$this->sendAction();
		$stats = Folding::loadTeamStats($foldingTeamId);
		$text = Folding::formatTeamStats($stats, $foldingTeamId);
		$this->reply($text);
	}
}