<?php

namespace TelegramWrapper\Inline;

use \Folding;
use \Icons;

class SetNickCommand extends Command
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		$this->reply(__CLASS__ . ' is in progress... Come back later.');
	}
}