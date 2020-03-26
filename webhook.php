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
$update->message->from->displayname = getDisplayName($update->message->from);

$db = \Factory::get_database();
$userData = $db->registerUser($update->message->from->id, $update->message->from->username);

$loop = Factory::create();
$tgLog = new TgLog(TELEGRAM_BOT_TOKEN, new HttpClientRequestHandler($loop));

$message = $update->message;
$sendMessage = new SendMessage();
$sendMessage->chat_id = $update->message->chat->id;
$sendMessage->parse_mode = 'HTML';

$command = getCommand($update);
$params = getParams($update);

//$foldingUser = $userData['user_folding_name'];
//$foldingTeam = '<i>unknown</i>';
//$foldingTeamId = 239186; // @TODO remove this temporary set team

$statsUrl = 'https://stats.foldingathome.org';

switch (mb_strtolower($command)) {
	case '/start':
		$sendMessage->text = 'Welcome to ' . TELEGRAM_BOT_NICK . '!';
		break;
	case '/help':
		$sendMessage->text = sprintf('%s Welcome to %s!', Icons::FOLDING, TELEGRAM_BOT_NICK) . PHP_EOL;
		$sendMessage->text .= sprintf('I\'m simple bot which help you to get statistics from <a href="%s">%s</a> website here into Telegram.', $statsUrl, $statsUrl) . PHP_EOL;
		$sendMessage->text .= PHP_EOL;
		$sendMessage->text .= sprintf(Icons::USER . ' <b>User commands</b>:') . PHP_EOL;

		$sendMessage->text .= sprintf(' /stats - load your personal statistics');
		if ($userData['user_folding_name']) {
			$sendMessage->text .= sprintf(' (user <a href="%s">%s</a>)', (getUserUrl($userData['user_folding_name'])), $userData['user_folding_name']);
		}
		$sendMessage->text .= PHP_EOL;

		$sendMessage->text .= sprintf(' /stats &lt;nick or ID&gt; - load specific user statistics') . PHP_EOL;
		$sendMessage->text .= sprintf(' /setNick &lt;nick or ID or URL&gt; - set default nick to different than your Telegram username.') . PHP_EOL;
		$sendMessage->text .= sprintf(Icons::TEAM . ' <b>Team commands</b>:') . PHP_EOL;

		$sendMessage->text .= sprintf(' /team - load your team statistics.');
		if ($userData['user_folding_team_id']) {
			$sendMessage->text .= sprintf(' Currently set to <a href="%s">%s</a>', (getTeamUrl($userData['user_folding_team_id'])), $userData['user_folding_team_name']);
		}
		$sendMessage->text .= PHP_EOL;

		$sendMessage->text .= sprintf(' /team &lt;team ID or URL&gt; - load specific team statistics') . PHP_EOL;
		$sendMessage->text .= PHP_EOL;
		$sendMessage->text .= sprintf('%s <b>Warning</b>: Website API is often very slow so be patient. Bot has automatic timeout set to %d seconds, then it will reply with sorry message.',
				Icons::WARNING,
				FOLDING_STATS_TIMEOUT
			) . PHP_EOL;
		break;
	case '/stats':
		if (isset($params[0])) {
			// parameter is URL with donor
			if (mb_strpos($params[0], getUserUrl('')) === 0) {
				$foldingUser = htmlentities(str_replace(getUserUrl(''), '', $params[0]));
			} else {
				// @TODO do some preg_match(), probably "^[^a-zA-Z0-9_%-]+$" (worked for top 100 on 2020-03-25)
				$foldingUser = htmlentities($params[0]);
			}
		}
		if ($foldingUser === null) {
			$sendMessage->text = sprintf('You have to set your nick first via /setNick &lt;nick or ID or URL&gt;') . PHP_EOL;
			break;
		} else {
			$chatAction = new SendChatAction();
			$chatAction->chat_id = $sendMessage->chat_id;
			$chatAction->action = 'typing';
			$tgLog->performApiRequest($chatAction);
			$loop->run();

			$stats = loadUserStats($foldingUser);
			if ($stats === null) { // Request error
				$sendMessage->text = sprintf('<a href="%s">%s</a>\'s folding stats from %s:', getUserUrl($foldingUser), $foldingUser, TELEGRAM_BOT_NICK) . PHP_EOL;
				$sendMessage->text .= sprintf('%s <b>Error</b>: Folding@home API is probably not available, try again later', Icons::ERROR) . PHP_EOL;
				break;
			}
			if (isset($stats->error)) { // API error
				// @TODO if error occured (for example not found, it has 404, so wrapper returns null
				$sendMessage->text = sprintf('<a href="%s">%s</a>\'s folding stats from %s:', getUserUrl($foldingUser), $foldingUser, TELEGRAM_BOT_NICK) . PHP_EOL;
				$sendMessage->text .= sprintf('%s <b>Error</b> from Folding@home: <i>%s</i>', Icons::ERROR, htmlentities($stats->error)) . PHP_EOL;
				break;
			}
			// Success!
			$sendMessage->text = sprintf('<a href="%s">%s</a>\'s folding stats from %s:', getUserUrl($foldingUser), $stats->name, TELEGRAM_BOT_NICK) . PHP_EOL;
			$sendMessage->text .= sprintf('%s <b>Credit</b>: %s (%s %s of %s users)',
					Icons::STATS_CREDIT,
					Utils::numberFormat($stats->credit),
					Icons::STATS_CREDIT_RANK,
					Utils::numberFormat($stats->rank),
					Utils::numberFormat($stats->total_users)
				) . PHP_EOL;
			$sendMessage->text .= sprintf('%s <b>WUs</b>: %s (<a href="%s">certificate</a>)',
					Icons::STATS_WU,
					Utils::numberFormat($stats->wus),
					$stats->wus_cert . '&cachebuster=' . $stats->last
				) . PHP_EOL;
			//		$lastWUDone = new \DateTime($stats->last); // @TODO add "ago". Note: datetime is probably UTC+0, not sure how about summer time
			$sendMessage->text .= sprintf('%s <b>Last WU done</b>: %s',
					Icons::STATS_WU_LAST_DONE, $stats->last
				) . PHP_EOL;
			$sendMessage->text .= sprintf('%s‍ <b>Active client(s)</b>: %s / %s (last week / 50 days)',
					Icons::STATS_ACTIVE_CLIENTS,
					Utils::numberFormat($stats->active_7),
					Utils::numberFormat($stats->active_50)
				) . PHP_EOL;
		}
		break;
	case '/team':
		if (isset($params[0])) {
			// parameter is URL with donor
			if (mb_strpos($params[0], getTeamUrl('')) === 0) {
				$foldingTeamIdParam = str_replace(getTeamUrl(''), '', $params[0]);
				if (is_numeric($foldingTeamIdParam)) {
					$foldingTeamId = $foldingTeamIdParam;
				}
			} else {
				if (is_numeric($params[0])) {
					$foldingTeamId = $params[0];
				}
			}
		}
		$chatAction = new SendChatAction();
		$chatAction->chat_id = $sendMessage->chat_id;
		$chatAction->action = 'typing';
		$tgLog->performApiRequest($chatAction);
		$loop->run();

		$stats = loadTeamStats($foldingTeamId);
		if ($stats === null) { // Request error
			$sendMessage->text = sprintf('<a href="%s">%s</a>\'s team folding stats from %s:', getTeamUrl($foldingTeamId), $foldingTeamId, TELEGRAM_BOT_NICK) . PHP_EOL;
			$sendMessage->text .= sprintf('%s <b>Error</b>: Folding@home API is probably not available, try again later', Icons::ERROR) . PHP_EOL;
		} else if (isset($stats->error)) { // API error
			// @TODO if error occured (for example not found, it has 404, so wrapper returns null
			$sendMessage->text = sprintf('<a href="%s">%s</a>\'s team folding stats from %s:', getTeamUrl($foldingTeamId), $foldingTeamId, TELEGRAM_BOT_NICK) . PHP_EOL;
			$sendMessage->text .= sprintf('%s <b>Error</b> from Folding@home: <i>%s</i>', Icons::ERROR, htmlentities($stats->error)) . PHP_EOL;
		} else { // Success!
			$sendMessage->text = sprintf('<a href="%s">%s</a>\'s team folding stats from %s:', getTeamUrl($foldingTeamId), $stats->name, TELEGRAM_BOT_NICK) . PHP_EOL;
			$sendMessage->text .= sprintf('%s <b>Credit</b>: %s (%s %s of %s teams, %s %s / user)',
					Icons::STATS_CREDIT,
					Utils::numberFormat($stats->credit),
					Icons::STATS_CREDIT_RANK,
					Utils::numberFormat($stats->rank),
					Utils::numberFormat($stats->total_teams),
					Icons::AVERAGE,
					Utils::numberFormat($stats->credit / count($stats->donors))
				) . PHP_EOL;
			$sendMessage->text .= sprintf('%s <b>WUs</b>: %s (%s %s / user) <a href="%s">Certificate</a>',
					Icons::STATS_WU,
					Utils::numberFormat($stats->wus),
					Icons::AVERAGE,
					Utils::numberFormat($stats->wus / count($stats->donors), 2),
					$stats->wus_cert . '&cachebuster=' . $stats->last
				) . PHP_EOL;
//		$lastWUDone = new \DateTime($stats->last); // @TODO add "ago". Note: datetime is probably UTC+0, not sure how about summer time
			$sendMessage->text .= sprintf('%s <b>Last WU done</b>: %s', Icons::STATS_WU_LAST_DONE, $stats->last) . PHP_EOL;
			$sendMessage->text .= sprintf('%s‍ <b>Active client(s)</b>: %s (last 50 days, %s %s / user)',
					Icons::STATS_ACTIVE_CLIENTS,
					Utils::numberFormat($stats->active_50),
					Icons::AVERAGE,
					Utils::numberFormat($stats->active_50 / count($stats->donors), 2)
				) . PHP_EOL;
		}
		break;
	case null: // message without command
		if (isPM($update)) {
			$sendMessage->text = 'Hello!' . PHP_EOL . 'If you want to see your stats, use /stats or look into /help.';
		} else {
			// keep quiet in groups...
		}
		break;
	default: // unknown command
		$sendMessage->text = 'Sorry, I don\'t know command... Try /help to get list of all commands.'; // @TODO add info which command was written
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

function loadUserStats($user) {
	return Utils::requestJson(getUserUrl($user, true));
}

function loadTeamStats($teamId) {
	return Utils::requestJson(getTeamUrl($teamId, true));
}

function getUserUrl(string $user, bool $api = false): string {
	$baseUrl = FOLDING_STATS_URL;
	if ($api === true) {
		$baseUrl .= '/api';
	}
	$baseUrl .= '/donor/' . $user;
	return $baseUrl;
}

function getTeamUrl($teamId, bool $api = false): string {
	$baseUrl = FOLDING_STATS_URL;
	if ($api === true) {
		$baseUrl .= '/api';
	}
	$baseUrl .= '/team/' . $teamId;
	return $baseUrl;
}

function getCommand($update): ?string {
	foreach ($update->message->entities as $entity) {
		if ($entity->offset === 0 && $entity->type === 'bot_command') {
			return mb_strcut($update->message->text, $entity->offset, $entity->length);
		}
	}
	return null;
}

function getParams($update): array {
	$text = $update->message->text;
	$params = explode(' ', $text);
	array_shift($params);
	return $params;
}

function isPM($update): bool {
	return ($update->message->from->id === $update->message->chat->id);
}

function getDisplayName($tgfrom) {
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
