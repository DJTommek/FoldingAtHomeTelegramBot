<?php

namespace TelegramWrapper\Inline;

use \Folding;
use \Icons;
use Tracy\Debugger;

class SetNickInline extends Inline
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		$foldingUserId = intval($this->params[0]);
		$foldingUserName = base64_decode($this->params[1]);
		$foldingTeamId = intval($this->params[2]);
		$foldingTeamName = base64_decode($this->params[3]);

		Debugger::log($this->params);
		$user->update(null, $foldingUserId, $foldingUserName, $foldingTeamId, $foldingTeamName);
		$msg = sprintf('%s Nick "%s" was set as default, now you can use command /stats without parameter.', Icons::SUCCESS, htmlentities($foldingUserName));
		if ($foldingTeamId) { // ignoring setting team do default, which is zero. User can do it manually via /team and there set as default
			$msg .= PHP_EOL . sprintf('Same applies to /team command to get statistics of "%s".', htmlentities($foldingTeamName));
		}
		$this->flash($msg, true);
	}
}