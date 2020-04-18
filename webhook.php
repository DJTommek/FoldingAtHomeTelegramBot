<?php
declare(strict_types=1);
echo '<pre>';

require_once __DIR__ . '/src/config.php';

use unreal4u\TelegramAPI\Telegram\Methods\SendChatAction;
use \unreal4u\TelegramAPI\Telegram\Types\Update;
use \React\EventLoop\Factory;
use \unreal4u\TelegramAPI\HttpClientRequestHandler;
use \unreal4u\TelegramAPI\Telegram\Methods\SendMessage;
use \unreal4u\TelegramAPI\TgLog;

$updateData = json_decode(file_get_contents('php://input'), true);

Logs::write('Webhook update data: ' . json_encode($updateData));
$update = new Update($updateData);

// @TODO this detection should be via secret parameter in URL
// @TODO add some info to maintainer how to setup webhook
if ($update->update_id === 0) {
	die('Telegram webhook API data are missing! This page should be requested only from Telegram servers via webhook.');
}

$loop = Factory::create();
$tgLog = new TgLog(TELEGRAM_BOT_TOKEN, new HttpClientRequestHandler($loop));

$command = TelegramWrapper\Telegram::getCommand($update);
$params = TelegramWrapper\Telegram::getParams($update);

// tweaks to data from Telegram library
if (TelegramWrapper\Telegram::isButtonClick($update)) {
	$update->callback_query->from->username = $update->callback_query->from->username === '' ? null : $update->callback_query->from->username;
	$update->callback_query->from->displayname = TelegramWrapper\Telegram::getDisplayName($update->callback_query->from);
	$user = new User($update->callback_query->from->id, $update->callback_query->from->username);

	switch ($command ? mb_strtolower($command) : null) {
		case \TelegramWrapper\Command\Command::CMD_DONOR: // @TODO shouldn't use from Command class, but Inline.
		case '/stats': // @TODO backward compatibility
			new \TelegramWrapper\Inline\StatsInline($update, $tgLog, $loop, $user);
			break;
		case \TelegramWrapper\Command\Command::CMD_TEAM: // @TODO shouldn't use from Command class, but Inline.
			new \TelegramWrapper\Inline\TeamInline($update, $tgLog, $loop, $user);
			break;
		case '/setnick':
			new \TelegramWrapper\Inline\SetNickInline($update, $tgLog, $loop, $user);
			break;
		case '/setteam':
			new \TelegramWrapper\Inline\SetTeamInline($update, $tgLog, $loop, $user);
			break;
		case '/settings':
			new \TelegramWrapper\Inline\SettingsInline($update, $tgLog, $loop, $user);
			break;
		default: // unknown
			// @TODO log error, this should not happen. Edit: can happen if some command is no longer used (for example /stats was changed to /donor)
			break;
	}
} else {
	$update->message->from->username = $update->message->from->username === '' ? null : $update->message->from->username;
	$update->message->from->displayname = TelegramWrapper\Telegram::getDisplayName($update->message->from);
	$user = new User($update->message->from->id, $update->message->from->username);

	switch ($command ? mb_strtolower($command) : null) {
		case '/start':
			new \TelegramWrapper\Command\StartCommand($update, $tgLog, $loop, $user);
			break;
		case '/help':
			new \TelegramWrapper\Command\HelpCommand($update, $tgLog, $loop, $user);
			break;
		case '/settings':
			new \TelegramWrapper\Command\SettingsCommand($update, $tgLog, $loop, $user);
			break;
		case '/stats':
			new \TelegramWrapper\Command\StatsCommand($update, $tgLog, $loop, $user);
			break;
		case \TelegramWrapper\Command\Command::CMD_DONOR:
			new \TelegramWrapper\Command\DonorCommand($update, $tgLog, $loop, $user);
			break;
		case \TelegramWrapper\Command\Command::CMD_TEAM:
			new \TelegramWrapper\Command\TeamCommand($update, $tgLog, $loop, $user);
			break;
		case null: // message without command
			new \TelegramWrapper\Command\MessageCommand($update, $tgLog, $loop, $user);
			break;
		default: // unknown command
			new \TelegramWrapper\Command\UnknownCommand($update, $tgLog, $loop, $user);
			break;
	}

}


