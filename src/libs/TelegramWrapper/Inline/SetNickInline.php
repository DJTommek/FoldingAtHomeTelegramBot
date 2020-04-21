<?php

namespace TelegramWrapper\Inline;

use \Folding;
use \Icons;
use TelegramWrapper\Command\Command;
use Tracy\Debugger;

class SetNickInline extends Inline
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop, $user);

		$foldingUserId = intval($this->params[0]);
		$foldingUserName = base64_decode($this->params[1]);

		if ($user->getFoldingId() === $foldingUserId) {
			$this->flash(sprintf('%s Nick "%s" is already your default, you can use command "%s" without parameter.', Icons::INFO, $user->getFoldingName(), Command::CMD_DONOR), true);
			return;
		}

		Debugger::log($this->params);
		$user->update(null, $foldingUserId, $foldingUserName);
		$msg = sprintf('%s Nick "%s" was set as default, now you can use command "%s" without parameter.', Icons::SUCCESS, htmlentities($foldingUserName), Command::CMD_DONOR);
		$this->flash($msg, true);
	}
}