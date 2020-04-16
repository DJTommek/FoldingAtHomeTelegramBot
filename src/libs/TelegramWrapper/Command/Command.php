<?php

namespace TelegramWrapper\Command;


use Folding;
use FoldingAtHome\Exceptions\ApiErrorException;
use FoldingAtHome\Exceptions\ApiTimeoutException;
use FoldingAtHome\Exceptions\GeneralException;
use FoldingAtHome\Exceptions\NotFoundException;
use FoldingAtHome\RequestUser;
use Icons;
use React\EventLoop\StreamSelectLoop;
use TelegramWrapper\Telegram;
use Tracy\Debugger;
use Tracy\ILogger;
use unreal4u\TelegramAPI\Telegram\Methods\SendChatAction;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
use unreal4u\TelegramAPI\Telegram\Types\Update;
use unreal4u\TelegramAPI\TgLog;

abstract class Command
{
	protected $update;
	protected $tgLog;
	protected $loop;

	protected $command = null;
	protected $params = [];

	public function __construct(Update $update, TgLog $tgLog, StreamSelectLoop $loop) {
		$this->update = $update;
		$this->tgLog = $tgLog;
		$this->loop = $loop;

		$this->command = Telegram::getCommand($update);
		$this->params = Telegram::getParams($update);
	}

	public function getChatId() {
		return $this->update->message->chat->id;
	}

	public function getFromId() {
		return $this->update->message->from->id;
	}

	public function isPm() {
		return Telegram::isPM($this->update);
	}

	public function reply(string $text, $replyMarkup = null) {
		$msg = new \TelegramWrapper\SendMessage($this->getChatId(), $text, null, $replyMarkup);
		$this->run($msg->msg);
	}

	public function sendAction(string $action = 'typing') {
		$chatAction = new SendChatAction();
		$chatAction->chat_id = $this->getChatId();
		$chatAction->action = $action;
		$this->run($chatAction);
	}

	public function run($objectToSend) {
		$promise = $this->tgLog->performApiRequest($objectToSend);
		$this->loop->run();

		$promise->then(
			function ($response) {
				Debugger::log($response);
				Debugger::log('TG API Request successfull. Response: ' . $response);
			},
			function (\Exception $exception) {
				Debugger::log(sprintf('Error while API request: "%s"', $exception->getMessage()), ILogger::EXCEPTION);
				throw $exception;
			}
		);
	}

	/**
	 * @param $foldingUserId
	 * @throws GeneralException
	 */
	protected function processStatsDonor($foldingUserId) {
		$this->sendAction();
		$replyMarkup = new Markup();

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