<?php

namespace TelegramWrapper;


use unreal4u\TelegramAPI\Telegram\Methods\EditMessageText;

class SendMessage
{
	public $msg;

	public function __construct(int $chatId, string $text, $replyMessageId = null, $replyMarkup = null, $messageId = null) {
		if (is_null($messageId)) {
			$this->msg = new \unreal4u\TelegramAPI\Telegram\Methods\SendMessage();
		} else {
			$this->msg = new \unreal4u\TelegramAPI\Telegram\Methods\EditMessageText();
			$this->msg->message_id = $messageId;
		}
		$this->msg->text = $text;
		$this->msg->chat_id = $chatId;
		$this->msg->reply_to_message_id = $replyMessageId;
		$this->msg->reply_markup = $replyMarkup;
		$this->setParseMode('HTML');
	}

	public function setParseMode($parseMode) {
		$this->msg->parse_mode = $parseMode;
	}

	public function appendMsg($text, ...$sprintfParams) {
		$this->msg->text .= vsprintf($text, $sprintfParams);
	}
}