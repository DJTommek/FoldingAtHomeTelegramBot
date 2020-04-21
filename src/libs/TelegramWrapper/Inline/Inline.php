<?php

namespace TelegramWrapper\Inline;


use React\EventLoop\StreamSelectLoop;
use TelegramWrapper\Telegram;
use Tracy\Debugger;
use Tracy\ILogger;
use unreal4u\TelegramAPI\Telegram\Methods\AnswerCallbackQuery;
use unreal4u\TelegramAPI\Telegram\Methods\SendChatAction;
use unreal4u\TelegramAPI\Telegram\Types\Update;
use unreal4u\TelegramAPI\TgLog;

abstract class Inline
{
	protected $update;
	protected $tgLog;
	protected $loop;
	protected $user;

	protected $command = null;
	protected $params = [];

	public function __construct(Update $update, TgLog $tgLog, StreamSelectLoop $loop, \User $user) {
		$this->update = $update;
		$this->tgLog = $tgLog;
		$this->loop = $loop;
		$this->user = $user;

		$this->command = Telegram::getCommand($update);
		$this->params = Telegram::getParams($update);
	}

	public function getChatId() {
		return $this->update->callback_query->message->chat->id;
	}

	public function getFromId() {
		return $this->update->callback_query->message->from->id;
	}

	public function isPm() {
		return Telegram::isPM($this->update);
	}

	public function replyButton(string $text, $replyMarkup = null, $editMessage = true) {
		$msg = new \TelegramWrapper\SendMessage($this->getChatId(), $text, null, $replyMarkup, $editMessage ? $this->update->callback_query->message->message_id : null);
		$this->run($msg->msg);
	}

	public function flash(string $text, bool $alert = false) {
		$flash = new AnswerCallbackQuery();
		$flash->text = $text;
		$flash->show_alert = $alert;
		$flash->callback_query_id = $this->update->callback_query->id;
		$this->run($flash);
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
				Debugger::log('TG API Inline request successfull. Response: ' . $response);
			},
			function (\Exception $exception) {
				Debugger::log(sprintf('TG API Inline request error: "%s"', $exception->getMessage()), ILogger::EXCEPTION);
				Debugger::log($exception, ILogger::EXCEPTION);
			}
		);

	}
}