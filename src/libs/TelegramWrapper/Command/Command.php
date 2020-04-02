<?php

namespace TelegramWrapper\Command;


use TelegramWrapper\Telegram;
use Tracy\Debugger;
use unreal4u\TelegramAPI\Telegram\Methods\AnswerCallbackQuery;
use unreal4u\TelegramAPI\Telegram\Methods\SendChatAction;

abstract class Command
{
	private $update;
	private $tgLog;
	private $loop;

	protected $command = null;
	protected $params = [];

	public function __construct($update, $tgLog, $loop) {
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
				Debugger::log('TG API Request successfull. Response: ' . $response);
			},
			function (\Exception $exception) {
				throw $exception;
			}
		);

	}
}