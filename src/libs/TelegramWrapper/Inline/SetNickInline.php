<?php

namespace TelegramWrapper\Inline;

use \Folding;
use \Icons;

class SetNickInline extends Inline
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		[$foldingUserName, $foldingUserId, $foldingTeamId, $foldingTeamName] = $this->params;
		$user->update($user->getTelegramId(), null, $foldingUserId, $foldingUserName, $foldingTeamId, $foldingTeamName);
		$msg = sprintf('%s Nick "%s" was set as default, now you can use command /stats without parameter.', Icons::SUCCESS, htmlentities($foldingUserName));
		if ($foldingTeamId) {
			$msg .= PHP_EOL . sprintf('Same applies to /team command to get statistics of "%s".', htmlentities($foldingTeamName));
		}
		$this->flash($msg, true);
	}
}