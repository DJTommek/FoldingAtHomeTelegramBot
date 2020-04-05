<?php

namespace TelegramWrapper\Command;

use \Folding;
use FoldingAtHome\UserAbstract;
use \Icons;

class StartCommand extends Command
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		$message = sprintf('%s Welcome to %s!', Icons::FOLDING, TELEGRAM_BOT_NICK) . PHP_EOL;
		$message .= sprintf('Simple bot which help you to get statistics from <a href="%s">%s</a> website here into Telegram.', Folding::STATS_URL, Folding::STATS_URL) . PHP_EOL;
		$message .= sprintf('Check /help for get list of commands.') . PHP_EOL;
		$message .= PHP_EOL;
		// nick was guessed based on Telegram username
		if ($user->getTelegramUsername() && $user->getFoldingId() === UserAbstract::DEFAULT_ID && $user->getFoldingName() !== UserAbstract::DEFAULT_NAME) {
		$message .= sprintf('%s <b>Note</b>: Folding@home username was guessed based on Telegram username which is "%s". You can change it to anything you want, just check /help', Icons::INFO, $user->getTelegramUsername()) . PHP_EOL;
		$message .= PHP_EOL;
		}
		$message .= sprintf('%s <b>Warning</b>: Website API is often very slow so be patient. Bot has automatic timeout set to %d seconds, then it will reply with sorry message.',
				Icons::WARNING, FOLDING_STATS_TIMEOUT) . PHP_EOL;

		$this->reply($message);
	}
}