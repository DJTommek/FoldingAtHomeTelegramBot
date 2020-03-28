<?php

namespace TelegramWrapper;


class SendMessage
{
	public $msg;

	public function __construct(int $chatId, string $text, $replyMessageId = null) {
		$this->msg = new \unreal4u\TelegramAPI\Telegram\Methods\SendMessage();
		$this->msg->text = $text;
		$this->msg->chat_id = $chatId;
		$this->msg->reply_to_message_id = $replyMessageId;
		$this->setParseMode('HTML');
	}
	public function setParseMode($parseMode) {
		$this->msg->parse_mode = $parseMode;
	}
	public function appendMsg($text, ...$sprintfParams) {
		$this->msg->text .= vsprintf($text, $sprintfParams);
	}
}