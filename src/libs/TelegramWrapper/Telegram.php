<?php

namespace TelegramWrapper;

class Telegram
{
	public static function getDisplayName($tgfrom) {
		if ($tgfrom->username) {
			$displayName = '@' . $tgfrom->username;
		} else {
			$displayName = '';
			$displayName .= ($tgfrom->first_name || ''); // first_name probably fill be always filled
			$displayName .= ' ';
			$displayName .= ($tgfrom->last_name || ''); // might be empty
		}
		return trim(htmlentities($displayName));
	}

	public static function isButtonClick($update): bool {
		return (!empty($update->callback_query));
	}

	public static function isPM($update): bool {
		if (self::isButtonClick($update)) {
			$message = $update->callback_query->message;
		} else {
			$message = $update->message;
		}
		return ($message->from->id === $message->chat->id);
	}

	public static function getCommand($update): ?string {
		if (self::isButtonClick($update)) {
			$fullCommand = $update->callback_query->data;
			return explode(' ', $fullCommand)[0];
		} else {
			foreach ($update->message->entities as $entity) {
				if ($entity->offset === 0 && $entity->type === 'bot_command') {
					return mb_strcut($update->message->text, $entity->offset, $entity->length);
				}
			}
			return null;
		}
	}

	public static function getParams($update): array {
		if (self::isButtonClick($update)) {
			$text = $update->callback_query->data;
		} else {
			$text = $update->message->text;
		}
		$params = explode(' ', $text);
		array_shift($params);
		return $params;
	}
}