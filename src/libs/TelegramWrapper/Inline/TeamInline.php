<?php

namespace TelegramWrapper\Inline;

use \Folding;
use FoldingAtHome\Exceptions\ApiErrorException;
use FoldingAtHome\Exceptions\ApiTimeoutException;
use FoldingAtHome\Exceptions\GeneralException;
use FoldingAtHome\Exceptions\NotFoundException;
use FoldingAtHome\RequestTeam;
use \Icons;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;

class TeamInline extends Inline
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		if (isset($this->params[0])) {
			$foldingTeamId = htmlentities($this->params[0]);
		} else {
			$this->flash('Invalid button, parameter is not set.', true);
			return;
		}

		try {
			$teamStats = (new RequestTeam($foldingTeamId))->load();
		} catch (NotFoundException $exception) {
			$this->flash(sprintf('%s Team %s not found', Icons::ERROR, htmlentities($foldingTeamId)));
			return;
		} catch (ApiErrorException $exception) {
			$this->flash(sprintf('%s Folding@home API responded with error: %s', Icons::ERROR, htmlentities($exception->getMessage())));
			return;
		} catch (ApiTimeoutException $exception) {
			$this->flash(sprintf('%s Folding@home API is not responding, try again later.', Icons::ERROR));
			return;
		} catch (GeneralException $exception) {
			$this->flash(sprintf('%s Unhandled Folding@home error occured, error was saved and admin was notified.', Icons::ERROR));
			throw $exception;
		}
		$text = Folding::formatTeamStats($teamStats);

		$replyMarkup = new Markup();
		$replyMarkup->inline_keyboard[] = [
			[
				'text' => sprintf('%s Refresh', Icons::REFRESH),
				'callback_data' => sprintf('/team %s', $foldingTeamId),
			], [
				'text' => Icons::DEFAULT . ' Set as default',
				'callback_data' => '/setteam ' . $teamStats->id . ' ' . $teamStats->name,
			],
		];
		$this->replyButton($text, $replyMarkup);
		$this->flash(sprintf('%s Team stats were refreshed!', Icons::SUCCESS));
	}
}