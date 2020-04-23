<?php

namespace TelegramWrapper\Inline;

use \Folding;
use \Icons;
use TelegramWrapper\Command\Command;

class SetTeamInline extends Inline
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop, $user);

		$foldingTeamId = intval($this->params[0]);
		$foldingTeamName = base64_decode($this->params[1]);

		if ($user->getFoldingTeamId() === $foldingTeamId) {
			$this->flash(sprintf('%s Team "%s" is already your default, you can use command "%s" without parameter.', Icons::INFO, htmlentities($user->getFoldingTeamName()), Command::CMD_TEAM), true);
			return;
		}

		$user->updateTeam($foldingTeamId, $foldingTeamName);
		$msg = sprintf('%s Team "%s" was set as default, now you can use command "%s" without parameter.', Icons::SUCCESS, htmlentities($foldingTeamName), Command::CMD_TEAM);
		$this->flash($msg, true);
	}
}