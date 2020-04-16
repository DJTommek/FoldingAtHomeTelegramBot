<?php

namespace TelegramWrapper\Inline;

use \Folding;
use FoldingAtHome\Exceptions\ApiErrorException;
use FoldingAtHome\Exceptions\ApiTimeoutException;
use FoldingAtHome\Exceptions\GeneralException;
use FoldingAtHome\Exceptions\NotFoundException;
use FoldingAtHome\RequestUser;
use \Icons;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Button;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
use unreal4u\TelegramAPI\Telegram\Types\KeyboardButton;
use unreal4u\TelegramAPI\Telegram\Types\ReplyKeyboardMarkup;

class StatsInline extends Inline
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop, $user);

		if (isset($this->params[0])) {
			$foldingUserId = htmlentities($this->params[0]);
		} else {
			$this->flash(sprintf('%s Invalid button, parameter is not set.', Icons::ERROR));
			return;
		}

		try {
			$userStats = (new RequestUser($foldingUserId))->load();
		} catch (NotFoundException $exception) {
			$this->flash(sprintf('%s User "%s" not found', Icons::ERROR, htmlentities($foldingUserId)), true);
			return;
		} catch (ApiErrorException $exception) {
			$this->flash(sprintf('%s Folding@home API responded with error: %s', Icons::ERROR, htmlentities($exception->getMessage())), true);
			return;
		} catch (ApiTimeoutException $exception) {
			$this->flash(sprintf('%s Folding@home API is not responding, try again later.', Icons::ERROR), true);
			return;
		} catch (GeneralException $exception) {
			$this->flash(sprintf('%s Unhandled Folding@home error occured, error was saved and admin was notified.', Icons::ERROR), true);
			throw $exception;
		}
		[$text, $buttons] = Folding::formatUserStats($userStats);

		$replyMarkup = new Markup();
		$replyMarkup->inline_keyboard[] = [
			[
				'text' => sprintf('%s Refresh', Icons::REFRESH),
				'callback_data' => sprintf('/stats %s', $foldingUserId),
			], [
				'text' => sprintf('%s Set donor as default', Icons::DEFAULT),
				'callback_data' => sprintf('/setnick %d %s', $userStats->id, base64_encode($userStats->name)),
			],
		];
		$replyMarkup->inline_keyboard = array_merge($replyMarkup->inline_keyboard, $buttons);
		$this->replyButton($text, $replyMarkup);
		$this->flash(sprintf('%s User stats were refreshed!', Icons::SUCCESS));
	}
}