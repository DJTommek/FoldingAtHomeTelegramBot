<?php

namespace TelegramWrapper\Command;

use \Folding;
use FoldingAtHome\Exceptions\ApiErrorException;
use FoldingAtHome\Exceptions\ApiTimeoutException;
use FoldingAtHome\Exceptions\GeneralException;
use FoldingAtHome\Exceptions\NotFoundException;
use FoldingAtHome\RequestUser;
use \Icons;
use Tracy\Debugger;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Button;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
use unreal4u\TelegramAPI\Telegram\Types\KeyboardButton;
use unreal4u\TelegramAPI\Telegram\Types\ReplyKeyboardMarkup;

class StatsCommand extends Command
{
	public function __construct($update, $tgLog, $loop, \User $user) {
		parent::__construct($update, $tgLog, $loop);

		// @TODO should be dynamically loaded from parent class
		$command = '/stats';

		$exampleText = sprintf('%s DJTommek', $command) . PHP_EOL;
		$exampleText .= sprintf('%s 68256828', $command) . PHP_EOL;
		$exampleText .= sprintf('%s https://stats.foldingathome.org/donor/DJTommek', $command) . PHP_EOL;

		$replyMarkup = new Markup();

		if (isset($this->params[0])) {
			// parameter is URL with donor ID
			if (mb_strpos($this->params[0], Folding::getUserUrl('')) === 0) {
				$foldingUserId = htmlentities(str_replace(Folding::getUserUrl(''), '', $this->params[0]));
			} else {
				// @TODO do some preg_match(), probably "^[^a-zA-Z0-9_%-]+$" (worked for top 100 on 2020-03-25)
				$foldingUserId = htmlentities($this->params[0]);
			}
			if (!$foldingUserId) {
				$this->reply(sprintf('%s <b>Error</b>: Parameter is not valid, it has to be nick, ID or valid URL. Examples: %s', Icons::ERROR, $exampleText));
				return;
			}
		} else {
			$foldingUserId = $user->getFoldingName();
			if (!$foldingUserId) {
				$msg = sprintf('%s <b>Error</b>: Missing required parameter nick, ID or URL. Examples:', Icons::ERROR) . PHP_EOL;
				$msg .= $exampleText;
				$msg .= PHP_EOL;
				$msg .= sprintf('%s PRO tip: load some user stats and then click on "%s Set as default". After that you can use %s command without parameters.', ICONS::INFO, ICONS::DEFAULT, $command) . PHP_EOL;
				$this->reply($msg);
				return;
			}
		}
		if (is_null($foldingUserId)) {
			$this->reply(sprintf('%s You have to set your nick first via /setNick &lt;nick or ID or URL&gt;', Icons::ERROR) . PHP_EOL);
			return;
		}
		$this->sendAction();
		try {
			$userStats = (new RequestUser($foldingUserId))->load();
		} catch (NotFoundException $exception) {
			$this->reply(sprintf('%s User <b>%s</b> not found', Icons::ERROR, htmlentities($foldingUserId)), $replyMarkup);
			return;
		} catch (ApiErrorException $exception) {
			$this->reply(sprintf('%s <b>Error</b>: Folding@home API responded with error <b>%s</b>', Icons::ERROR, htmlentities($exception->getMessage())), $replyMarkup);
			return;
		} catch (ApiTimeoutException $exception) {
			$replyMarkup->inline_keyboard[] = [
				$this->addRefreshButton($foldingUserId)
			];
			$this->reply(sprintf('%s <b>Error</b>: Folding@home API is not responding, try again later.', Icons::ERROR), $replyMarkup);
			return;
		} catch (GeneralException $exception) {
			$this->reply(sprintf('%s <b>Error</b>: Unhandled Folding@home error occured, error was saved and admin was notified.', Icons::ERROR), $replyMarkup);
			throw $exception;
		}
		[$text, $buttons] = Folding::formatUserStats($userStats);

		$replyMarkup->inline_keyboard[] = [
			$this->addRefreshButton($foldingUserId),
			[
				'text' => sprintf('%s Set donor as default', Icons::DEFAULT),
				'callback_data' => sprintf('/setnick %d %s', $userStats->id, base64_encode($userStats->name)),
			]
		];
		$replyMarkup->inline_keyboard = array_merge($replyMarkup->inline_keyboard, $buttons);
		$this->reply($text, $replyMarkup);
	}

	private function addRefreshButton($foldingUserId) {
		return [
			'text' => sprintf('%s Refresh', Icons::REFRESH),
			'callback_data' => sprintf('/stats %s', $foldingUserId),
		];
	}
}