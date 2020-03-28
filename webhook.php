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

// tweaks to data from Telegram library
if (TelegramWrapper\Telegram::isButtonClick($update)) {
	$update->callback_query->message->from->username = $update->callback_query->message->from->username === '' ? null : $update->callback_query->message->from->username;
	$update->callback_query->message->from->displayname = TelegramWrapper\Telegram::getDisplayName($update->callback_query->message->from);
	$user = new User($update->callback_query->message->from->id, $update->callback_query->message->from->username);
} else {
	$update->message->from->username = $update->message->from->username === '' ? null : $update->message->from->username;
	$update->message->from->displayname = TelegramWrapper\Telegram::getDisplayName($update->message->from);
	$user = new User($update->message->from->id, $update->message->from->username);
}

$loop = Factory::create();
$tgLog = new TgLog(TELEGRAM_BOT_TOKEN, new HttpClientRequestHandler($loop));

$command = TelegramWrapper\Telegram::getCommand($update);
$params = TelegramWrapper\Telegram::getParams($update);

switch ($command ? mb_strtolower($command) : null) {
	case '/start':
		new \TelegramWrapper\Command\StartCommand($update, $tgLog, $loop);
		break;
	case '/help':
		new \TelegramWrapper\Command\HelpCommand($update, $tgLog, $loop, $user);
		break;
	case '/stats':
		new \TelegramWrapper\Command\StatsCommand($update, $tgLog, $loop, $user);
		break;
	case '/team':
		new \TelegramWrapper\Command\TeamCommand($update, $tgLog, $loop, $user);
		break;
	case '/setnick':
		new \TelegramWrapper\Command\SetNickCommand($update, $tgLog, $loop, $user);
		break;
		if (!isset($params[0])) {
			$sendMessage->text = sprintf('%s <b>Error</b>: command %s is missing parameter. Examples:', Icons::ERROR, $command) . PHP_EOL;
			$sendMessage->text .= sprintf('%s DJTommek', $command) . PHP_EOL;
			$sendMessage->text .= sprintf('%s 68256828', $command) . PHP_EOL;
			$sendMessage->text .= sprintf('%s https://stats.foldingathome.org/donor/DJTommek', $command) . PHP_EOL;
			break;
		}
		// parameter is URL with donor
		if (mb_strpos($params[0], Folding::getUserUrl('')) === 0) {
			$foldingUser = htmlentities(str_replace(Folding::getUserUrl(''), '', $params[0]));
		} else {
			// @TODO do some preg_match(), probably "^[^a-zA-Z0-9_%-]+$" (worked for top 100 on 2020-03-25)
			$foldingUser = htmlentities($params[0]);
		}
		$stats = Folding::loadUserStats($foldingUser);
		if ($stats === null) { // Request error
			$sendMessage->text = sprintf('%s <b>Error</b>: Nick/ID "%s" is not valid or API is not available.', Icons::ERROR, $foldingUser) . PHP_EOL;
			break;
		}
		if (isset($stats->error)) { // API error
			// @TODO if error occured (for example not found, it has 404, so wrapper returns null
			$sendMessage->text .= sprintf('%s <b>Error</b> from Folding@home API: <i>%s</i>', Icons::ERROR, htmlentities($stats->error)) . PHP_EOL;
			break;
		}
		// Success!
		$sendMessage->text = sprintf('%s Nick <a href="%s">%s</a> (ID %d) is valid!', Icons::SUCCESS, Folding::getUserUrl($stats->name), $stats->name, $stats->id) . PHP_EOL;
		$foldingTeamId = null;
		$foldingTeamName = null;
		if (count($stats->teams) > 0) {
			$foldingTeamId = $stats->teams[0]->team;
			$foldingTeamName = $stats->teams[0]->name;
			$sendMessage->text .= sprintf('%s Default team set to <a href="%s">%s</a> (ID %d), you can change it via /setTeam.',
					Icons::SUCCESS, Folding::getTeamUrl($foldingTeamId), $foldingTeamName, $foldingTeamId) . PHP_EOL;
		}
		$user->update($update->message->from->id, null, $stats->id, $stats->name, $foldingTeamId, $foldingTeamName);
		$sendMessage->text .= sprintf('Now you can use command /stats %sto get these beautifull statistics.',
			$foldingTeamId ? 'or /team ' : '') . PHP_EOL;

		break;
	case '/setteam':
		new \TelegramWrapper\Command\SetTeamCommand($update, $tgLog, $loop, $user);
		break;
		if (!isset($params[0])) {
			$sendMessage->text = sprintf('%s <b>Error</b>: command %s is missing parameter. Examples:', Icons::ERROR, $command) . PHP_EOL;
			$sendMessage->text .= sprintf('%s 239186', $command) . PHP_EOL;
			$sendMessage->text .= sprintf('%s https://stats.foldingathome.org/team/239186', $command) . PHP_EOL;
			break;
		}
		// parameter is URL with team ID
		if (mb_strpos($params[0], Folding::getTeamUrl('')) === 0) {
			$foldingTeamId = str_replace(Folding::getTeamUrl(''), '', $params[0]);
		} else {
			$foldingTeamId = $params[0];
		}
		if (is_numeric($foldingTeamId)) {
			$foldingTeamId = intval($foldingTeamId);
		} else {
			$sendMessage->text = sprintf('%s <b>Error</b>: Team ID is not valid.', Icons::ERROR) . PHP_EOL;
			break;
		}
		$stats = Folding::loadTeamStats($foldingTeamId);
		if ($stats === null) { // Request error
			$sendMessage->text = sprintf('%s <b>Error</b>: Team ID "%d" is not valid or API is not available.', Icons::ERROR, $foldingTeamId) . PHP_EOL;
			break;
		}
		if (isset($stats->error)) { // API error
			// @TODO if error occured (for example not found, it has 404, so wrapper returns null
			$sendMessage->text .= sprintf('%s <b>Error</b> from Folding@home API: <i>%s</i>', Icons::ERROR, htmlentities($stats->error)) . PHP_EOL;
			break;
		}
		// Success!
		$sendMessage->text = sprintf('%s Team <a href="%s">%s</a> (ID %d) is valid!', Icons::SUCCESS, Folding::getTeamUrl($stats->team), $stats->name, $stats->team) . PHP_EOL;
//		$db->updateUser($update->message->from->id, null, $stats->id, $stats->name, $foldingTeamId, $foldingTeamName);
		$sendMessage->text .= sprintf('Now you can use command /team to get these beautifull statistics.') . PHP_EOL;
		break;
	case null: // message without command
		new \TelegramWrapper\Command\MessageCommand($update, $tgLog, $loop, $user);
		break;
	default: // unknown command
		new \TelegramWrapper\Command\UnknownCommand($update, $tgLog, $loop, $user);
		break;
}


