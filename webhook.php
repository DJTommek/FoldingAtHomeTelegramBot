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
$update->message->from->username = $update->message->from->username === '' ? null : $update->message->from->username;
$update->message->from->displayname = Telegram::getDisplayName($update->message->from);
$user = new User($update->message->from->id, $update->message->from->username);

$loop = Factory::create();
$tgLog = new TgLog(TELEGRAM_BOT_TOKEN, new HttpClientRequestHandler($loop));

$message = $update->message;
$sendMessage = new SendMessage();
$sendMessage->chat_id = $update->message->chat->id;
$sendMessage->reply_to_message_id = $update->message->message_id;
$sendMessage->parse_mode = 'HTML';

$command = Telegram::getCommand($update);
$params = Telegram::getParams($update);

//$foldingUser = $userData['user_folding_name'];
//$foldingTeam = '<i>unknown</i>';
//$foldingTeamId = 239186; // @TODO remove this temporary set team

switch ($command ? mb_strtolower($command) : null) {
	case '/start':
		$sendMessage->text = sprintf('%s Welcome to %s!', Icons::FOLDING, TELEGRAM_BOT_NICK) . PHP_EOL;
		$sendMessage->text .= sprintf('Simple bot which help you to get statistics from <a href="%s">%s</a> website here into Telegram.', Folding::STATS_URL, Folding::STATS_URL) . PHP_EOL;
		$sendMessage->text .= sprintf('If you want to see your stats, use /stats or look into /help.') . PHP_EOL;
		$sendMessage->text .= PHP_EOL;
		$sendMessage->text .= sprintf('%s <b>Warning</b>: Website API is often very slow so be patient. Bot has automatic timeout set to %d seconds, then it will reply with sorry message.',
				Icons::WARNING, FOLDING_STATS_TIMEOUT) . PHP_EOL;
		break;
	case '/help':
		$sendMessage->text = sprintf('%s Welcome to %s!', Icons::FOLDING, TELEGRAM_BOT_NICK) . PHP_EOL;
		$sendMessage->text .= sprintf('Simple bot which help you to get statistics from <a href="%s">%s</a> website here into Telegram.', Folding::STATS_URL, Folding::STATS_URL) . PHP_EOL;
		$sendMessage->text .= PHP_EOL;
		$sendMessage->text .= sprintf(Icons::USER . ' <b>User commands</b>:') . PHP_EOL;

		$sendMessage->text .= sprintf(' /stats - load your personal statistics');
		if ($user->getFoldingName()) {
			$sendMessage->text .= sprintf(' (currently set to user <a href="%s">%s</a>)', $user->getUrl(), $user->getFoldingName());
		}
		$sendMessage->text .= PHP_EOL;

		$sendMessage->text .= sprintf(' /stats &lt;nick or ID&gt; - load specific user statistics') . PHP_EOL;
		$sendMessage->text .= sprintf(' /setNick &lt;nick or ID or URL&gt; - set default nick to different than your Telegram username.') . PHP_EOL;
		$sendMessage->text .= PHP_EOL;
		$sendMessage->text .= sprintf(Icons::TEAM . ' <b>Team commands</b>:') . PHP_EOL;

		$sendMessage->text .= sprintf(' /team - load your team statistics.');
		if ($user->getFoldingTeamId()) {
			$sendMessage->text .= sprintf(' (currently set to team <a href="%s">%s</a>)', (Folding::getTeamUrl($user->getFoldingTeamId())), $user->getFoldingName());
		}
		$sendMessage->text .= PHP_EOL;

		$sendMessage->text .= sprintf(' /team &lt;team ID or URL&gt; - load specific team statistics') . PHP_EOL;
		$sendMessage->text .= sprintf(' /setTeam &lt;ID or URL&gt; - set default team') . PHP_EOL;
		$sendMessage->text .= PHP_EOL;
		$sendMessage->text .= sprintf('%s <b>Warning</b>: Website API is often very slow so be patient. Bot has automatic timeout set to %d seconds, then it will reply with sorry message.',
				Icons::WARNING, FOLDING_STATS_TIMEOUT) . PHP_EOL;
		break;
	case '/stats':
		if (isset($params[0])) {
			// parameter is URL with donor
			if (mb_strpos($params[0], Folding::getUserUrl('')) === 0) {
				$foldingUser = htmlentities(str_replace(Folding::getUserUrl(''), '', $params[0]));
			} else {
				// @TODO do some preg_match(), probably "^[^a-zA-Z0-9_%-]+$" (worked for top 100 on 2020-03-25)
				$foldingUser = htmlentities($params[0]);
			}
		} else {
			$foldingUser = $user->getFoldingName();
		}
		if ($foldingUser === null) {
			$sendMessage->text = sprintf('%s You have to set your nick first via /setNick &lt;nick or ID or URL&gt;', Icons::ERROR) . PHP_EOL;
			break;
		}
		$chatAction = new SendChatAction();
		$chatAction->chat_id = $sendMessage->chat_id;
		$chatAction->action = 'typing';
		$tgLog->performApiRequest($chatAction);
		$loop->run();

		$stats = Folding::loadUserStats($foldingUser);
		$sendMessage->text = Folding::formatUserStats($stats, $foldingUser);
		break;
	case '/team':
		if (isset($params[0])) {
			// parameter is URL with donor
			if (mb_strpos($params[0], Folding::getTeamUrl('')) === 0) {
				$foldingTeamIdParam = str_replace(Folding::getTeamUrl(''), '', $params[0]);
				if (is_numeric($foldingTeamIdParam)) {
					$foldingTeamId = $foldingTeamIdParam;
				}
			} else {
				if (is_numeric($params[0])) {
					$foldingTeamId = $params[0];
				}
			}
		} else {
			$foldingTeamId = $user->getFoldingTeamId();
		}
		if ($foldingTeamId === null) {
			$sendMessage->text = sprintf('%s You have to set your team first via /setTeam &lt;ID or URL&gt;', Icons::ERROR) . PHP_EOL;
			break;
		}

		$chatAction = new SendChatAction();
		$chatAction->chat_id = $sendMessage->chat_id;
		$chatAction->action = 'typing';
		$tgLog->performApiRequest($chatAction);
		$loop->run();

		$stats = Folding::loadTeamStats($foldingTeamId);
		$sendMessage->text = Folding::formatTeamStats($stats, $foldingTeamId);

		break;
	case '/setnick':
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
		if (Telegram::isPM($update)) {
			$sendMessage->text = sprintf('%s Welcome to %s!', Icons::FOLDING, TELEGRAM_BOT_NICK) . PHP_EOL;
			$sendMessage->text .= sprintf('If you want to see your stats, use /stats or look into /help.') . PHP_EOL;
			$sendMessage->text .= PHP_EOL;
			$sendMessage->text .= sprintf('%s <b>Warning</b>: Website API is often very slow so be patient. Bot has automatic timeout set to %d seconds, then it will reply with sorry message.',
					Icons::WARNING, FOLDING_STATS_TIMEOUT) . PHP_EOL;
		} else {
			// keep quiet in groups...
		}
		break;
	default: // unknown command
		$sendMessage->text = sprintf('%s Sorry, I don\'t know command...', Icons::ERROR) . PHP_EOL; // @TODO add info which command was written
		$sendMessage->text .= sprintf('Try /help to get list of all commands.');
		break;
}

$promise = $tgLog->performApiRequest($sendMessage);

$promise->then(
	function ($response) {
		dd($response, false);
	},
	function (\Exception $exception) {
		dd($exception, false);
	}
);
$loop->run();

