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
		parent::__construct($update, $tgLog, $loop, $user);

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
		[$text, $buttons] = Folding::formatTeamStats($teamStats);

		$replyMarkup = new Markup();
		$replyMarkup->inline_keyboard[] = [
			[
				'text' => sprintf('%s Refresh', Icons::REFRESH),
				'callback_data' => sprintf('/team %d', $foldingTeamId),
			], [
				'text' => Icons::DEFAULT . ' Set team as default',
				'callback_data' => sprintf('/setteam %d %s', $teamStats->id, base64_encode($teamStats->name)),
			],
		];
		$replyMarkup->inline_keyboard = array_merge($replyMarkup->inline_keyboard, $buttons);
		$this->replyButton($text, $replyMarkup);
		$this->flash(sprintf('%s Team stats were refreshed!', Icons::SUCCESS));
	}
}