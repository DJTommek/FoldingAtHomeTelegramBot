<?php

namespace TelegramWrapper\Command;

use \Folding;
use \Icons;

class SetTeamCommand extends Command
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		$this->reply(__CLASS__ . ' is in progress... Come back later.');
	}
}