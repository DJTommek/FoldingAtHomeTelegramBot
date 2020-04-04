<?php

namespace TelegramWrapper\Inline;

use \Folding;
use \Icons;

class SetTeamInline extends Inline
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		$foldingTeamId = intval($this->params[0]);
		$foldingTeamName = base64_decode($this->params[1]);

		$user->updateTeam(intval($foldingTeamId), $foldingTeamName);
		$msg = sprintf('%s Team "%s" was set as default, now you can use command /team without parameter.', Icons::SUCCESS, htmlentities($foldingTeamName));
		$this->flash($msg, true);
	}
}