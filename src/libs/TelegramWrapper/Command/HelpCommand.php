<?php

namespace TelegramWrapper\Command;

use \Folding;
use \Icons;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Button;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
use unreal4u\TelegramAPI\Telegram\Types\KeyboardButton;
use unreal4u\TelegramAPI\Telegram\Types\ReplyKeyboardMarkup;

class HelpCommand extends Command
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop, $user);

		$text = sprintf('%s Welcome to %s!', Icons::FOLDING, TELEGRAM_BOT_NICK) . PHP_EOL;
		$text .= sprintf('Simple bot which help you to get statistics from <a href="%s">%s</a> website here into Telegram.', \FoldingAtHome\Request::STATS_BASE_URL, \FoldingAtHome\Request::STATS_BASE_URL) . PHP_EOL;
		$text .= PHP_EOL;
		$text .= sprintf(Icons::DONOR . ' <b>Donor commands</b>:') . PHP_EOL;
		$text .= sprintf(' %s - load your personal statistics. Currently set to donor <a href="%s">%s</a>', Command::CMD_DONOR, $user->getUrl(), htmlentities($user->getFoldingName())) . PHP_EOL;
		$text .= sprintf(' %s &lt;nick, ID or URL&gt; - load specific donor statistics', Command::CMD_DONOR) . PHP_EOL;
		$text .= PHP_EOL;
		$text .= sprintf(Icons::TEAM . ' <b>Team commands</b>:') . PHP_EOL;
		$text .= sprintf(' %s - load your team statistics. Currently set to team <a href="%s">%s</a>', Command::CMD_TEAM, (Folding::getTeamUrl($user->getFoldingTeamId())), htmlentities($user->getFoldingTeamName())) . PHP_EOL;
		$text .= sprintf(' %s &lt;team ID or URL&gt; - load specific team statistics', Command::CMD_TEAM) . PHP_EOL;
		$text .= PHP_EOL;
		$text .= sprintf('%s <b>Warning</b>: Website API is often very slow so be patient. Bot has automatic timeout set to %d seconds, then it will reply with sorry message. In few seconds try again and response should be quick.',
				Icons::WARNING, FOLDING_STATS_TIMEOUT) . PHP_EOL;

		$this->reply($text);
	}
}