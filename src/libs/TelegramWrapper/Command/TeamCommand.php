<?php

namespace TelegramWrapper\Command;

use \Folding;
use FoldingAtHome\Exceptions\ApiErrorException;
use FoldingAtHome\Exceptions\ApiTimeoutException;
use FoldingAtHome\Exceptions\GeneralException;
use FoldingAtHome\Exceptions\NotFoundException;
use FoldingAtHome\RequestTeam;
use FoldingAtHome\RequestUser;
use \Icons;
use Tracy\Debugger;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;

class TeamCommand extends Command
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		$command = '/team';

		$exampleText = sprintf('%s 239186', $command) . PHP_EOL;
		$exampleText .= sprintf('%s https://stats.foldingathome.org/team/239186', $command) . PHP_EOL;

		$replyMarkup = new Markup();

		if (isset($this->params[0])) {
			// parameter is URL with donor
			if (mb_strpos($this->params[0], Folding::getTeamUrl('')) === 0) {
				$foldingTeamId = intval(str_replace(Folding::getTeamUrl(''), '', $this->params[0]));
			} else {
				$foldingTeamId = intval($this->params[0]);
			}
			if (!$foldingTeamId) { // is zero
				$this->reply(sprintf('%s <b>Error</b>: Parameter is not valid, it has to be Team ID or valid URL. Examples: %s', Icons::ERROR, $exampleText));
				return;
			}
		} else {
			$foldingTeamId = $user->getFoldingTeamId();
			if (!$foldingTeamId) {
				$msg = sprintf('%s <b>Error</b>: Missing required parameter Team ID or URL. Examples:', Icons::ERROR) . PHP_EOL;
				$msg .= $exampleText;
				$msg .= PHP_EOL;
				$msg .= sprintf('%s PRO tip: load some team stats and then click on "%s Set as default". After that you can use %s command without parameters.', ICONS::INFO, ICONS::DEFAULT, $command) . PHP_EOL;
				$this->reply($msg);
				return;
			}
		}
		if (is_null($foldingTeamId)) {
			$this->reply(sprintf('%s You have to set your team first via /setTeam &lt;ID or URL&gt;', Icons::ERROR) . PHP_EOL);
			return;
		}

		$this->sendAction();
		try {
			$teamStats = (new RequestTeam($foldingTeamId))->load();
		} catch (NotFoundException $exception) {
			$this->reply(sprintf('%s User <b>%s</b> not found', Icons::ERROR, htmlentities($foldingTeamId)), $replyMarkup);
			return;
		} catch (ApiErrorException $exception) {
			$this->reply(sprintf('%s <b>Error</b>: Folding@home API responded with error <b>%s</b>', Icons::ERROR, htmlentities($exception->getMessage())), $replyMarkup);
			return;
		} catch (ApiTimeoutException $exception) {
			$replyMarkup->inline_keyboard[] = $this->addRefreshButton($foldingTeamId);
			$this->reply(sprintf('%s <b>Error</b>: Folding@home API is not responding, try again later.', Icons::ERROR), $replyMarkup);
			return;
		} catch (GeneralException $exception) {
			$this->reply(sprintf('%s <b>Error</b>: Unhandled Folding@home error occured, error was saved and admin was notified.', Icons::ERROR), $replyMarkup);
			throw $exception;
		}
		$text = Folding::formatTeamStats($teamStats);

		$replyMarkup = new Markup();
		$replyMarkup->inline_keyboard[] = $this->addRefreshButton($teamStats->id);
		$replyMarkup->inline_keyboard[0][] = [
			'text' => Icons::DEFAULT . ' Set as default',
			'callback_data' => '/setteam ' . $teamStats->id . ' ' . $teamStats->name,
		];

		$this->reply($text, $replyMarkup);
	}

	private function addRefreshButton($foldingUserId) {
		return [
			[
				'text' => sprintf('%s Refresh', Icons::REFRESH),
				'callback_data' => sprintf('/team %s', $foldingUserId),
			],
		];
	}
}