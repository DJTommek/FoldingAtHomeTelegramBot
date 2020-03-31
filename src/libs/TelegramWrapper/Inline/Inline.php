<?php

namespace TelegramWrapper\Inline;


use TelegramWrapper\Telegram;
use unreal4u\TelegramAPI\Telegram\Methods\AnswerCallbackQuery;
use unreal4u\TelegramAPI\Telegram\Methods\SendChatAction;

abstract class Inline
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
		return $this->update->callback_query->message->chat->id;
	}

	public function getFromId() {
		return $this->update->callback_query->message->from->id;
	}

	public function isPm() {
		return Telegram::isPM($this->update);
	}

	public function replyButton(string $text, $replyMarkup = null) {
		$msg = new \TelegramWrapper\SendMessage($this->getChatId(), $text, null, $replyMarkup, $this->update->callback_query->message->message_id);
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
				dd($response, false);
			},
			function (\Exception $exception) {
				dd($exception, false);
			}
		);

	}
}