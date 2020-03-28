<?php

class Folding
{
	const STATS_URL = 'https://stats.foldingathome.org';

	public static function loadUserStats($user) {
		return Utils::requestJson(self::getUserUrl($user, true));
	}

	public static function loadTeamStats($teamId) {
		return Utils::requestJson(self::getTeamUrl($teamId, true));
	}

	public static function getUserUrl(string $user, bool $api = false): string {
		$baseUrl = self::STATS_URL;
		if ($api === true) {
			$baseUrl .= '/api';
		}
		$baseUrl .= '/donor/' . $user;
		return $baseUrl;
	}

	public static function getTeamUrl($teamId, bool $api = false): string {
		$baseUrl = self::STATS_URL;
		if ($api === true) {
			$baseUrl .= '/api';
		}
		$baseUrl .= '/team/' . $teamId;
		return $baseUrl;
	}

	public static function formatUserStats($stats, $foldingUser) {

		// Request error
		if ($stats === null) {
			$message = sprintf('%s\'s folding stats from %s:', Folding::formatUserLink($foldingUser), TELEGRAM_BOT_NICK) . PHP_EOL;
			$message .= sprintf('%s <b>Error</b>: Folding@home API is probably not available, try again later', Icons::ERROR) . PHP_EOL;
			return $message;
		}

		// API error
		// @TODO if error occured (for example not found, it has 404, so wrapper returns null
		if (isset($stats->error)) {
			$message = sprintf('<a href="%s">%s</a>\'s folding stats from %s:', Folding::getUserUrl($foldingUser), $foldingUser, TELEGRAM_BOT_NICK) . PHP_EOL;
			$message .= sprintf('%s <b>Error</b> from Folding@home: <i>%s</i>', Icons::ERROR, htmlentities($stats->error)) . PHP_EOL;
			return $message;
		}

		// Success!
		$message = sprintf('%s\'s folding stats from %s:', Folding::formatUserLink($stats->name), TELEGRAM_BOT_NICK) . PHP_EOL;
		$message .= sprintf('%s <b>Credit</b>: %s (%s %s of %s users)',
				Icons::STATS_CREDIT,
				Utils::numberFormat($stats->credit),
				Icons::STATS_CREDIT_RANK,
				$stats->rank > 0 ? Utils::numberFormat($stats->rank) : '?',
				Utils::numberFormat($stats->total_users)
			) . PHP_EOL;
		$message .= sprintf('%s <b>WUs</b>: %s (<a href="%s">certificate</a>)',
				Icons::STATS_WU,
				Utils::numberFormat($stats->wus),
				$stats->wus_cert . '&cachebuster=' . $stats->last
			) . PHP_EOL;
		//		$lastWUDone = new \DateTime($stats->last); // @TODO add "ago". Note: datetime is probably UTC+0, not sure how about summer time
		$message .= sprintf('%s <b>Last WU done</b>: %s',
				Icons::STATS_WU_LAST_DONE, $stats->last
			) . PHP_EOL;
		$message .= sprintf('%s‍ <b>Active client(s)</b>: %s / %s (last week / 50 days)',
				Icons::STATS_ACTIVE_CLIENTS,
				Utils::numberFormat($stats->active_7),
				Utils::numberFormat($stats->active_50)
			) . PHP_EOL;
		return $message;
	}

	public static function formatTeamStats($stats, $foldingTeamId) {
		// Request error
		if ($stats === null) {
			$message = sprintf('<a href="%s">%s</a>\'s team folding stats from %s:', self::getTeamUrl($foldingTeamId), $foldingTeamId, TELEGRAM_BOT_NICK) . PHP_EOL;
			$message .= sprintf('%s <b>Error</b>: Folding@home API is probably not available, try again later', Icons::ERROR) . PHP_EOL;
			return $message;
		}
		// API error
		// @TODO if error occured (for example not found, it has 404, so wrapper returns null
		if (isset($stats->error)) {
			$message = sprintf('<a href="%s">%s</a>\'s team folding stats from %s:', self::getTeamUrl($foldingTeamId), $foldingTeamId, TELEGRAM_BOT_NICK) . PHP_EOL;
			$message .= sprintf('%s <b>Error</b> from Folding@home: <i>%s</i>', Icons::ERROR, htmlentities($stats->error)) . PHP_EOL;
			return $message;
		}

		// Success!
		$message = sprintf('<a href="%s">%s</a>\'s team folding stats:', self::getTeamUrl($foldingTeamId), $stats->name) . PHP_EOL;
		$message .= sprintf('%s <b>Credit</b>: %s (%s %s of %s teams, %s %s / user)',
				Icons::STATS_CREDIT,
				Utils::numberFormat($stats->credit),
				Icons::STATS_CREDIT_RANK,
				Utils::numberFormat($stats->rank),
				Utils::numberFormat($stats->total_teams),
				Icons::AVERAGE,
				Utils::numberFormat($stats->credit / count($stats->donors))
			) . PHP_EOL;
		$message .= sprintf('%s <b>WUs</b>: %s (%s %s / user) <a href="%s">Certificate</a>',
				Icons::STATS_WU,
				Utils::numberFormat($stats->wus),
				Icons::AVERAGE,
				Utils::numberFormat($stats->wus / count($stats->donors), 2),
				$stats->wus_cert . '&cachebuster=' . $stats->last
			) . PHP_EOL;
//		$lastWUDone = new \DateTime($stats->last); // @TODO add "ago". Note: datetime is probably UTC+0, not sure how about summer time
		$message .= sprintf('%s <b>Last WU done</b>: %s', Icons::STATS_WU_LAST_DONE, $stats->last) . PHP_EOL;
		$message .= sprintf('%s‍ <b>Active client(s)</b>: %s (last 50 days, %s %s / user)',
				Icons::STATS_ACTIVE_CLIENTS,
				Utils::numberFormat($stats->active_50),
				Icons::AVERAGE,
				Utils::numberFormat($stats->active_50 / count($stats->donors), 2)
			) . PHP_EOL;

		// Show top x donors but only if at least two donors are available
		if (count($stats->donors) >= 2) {
			$message .= PHP_EOL;
			$message .= Folding::formatTeamStatsTop($stats->donors);
		}
		return $message;
	}

	public static function formatUserLink($user) {
		return sprintf('<a href="%s">%s</a>', self::getUserUrl($user), $user);
	}

	public static function formatTeamStatsTop($donors, $count = 5) {
		$showing = min(count($donors), $count);
		if ($showing === 0) {
			return null;
		}
		$message = sprintf('%s <b>Top %d donors</b>:', Icons::STATS_TEAM_TOP, $showing) . PHP_EOL;
		foreach ($donors as $i => $donor) {
			if ($i >= $showing) {
				break;
			}
			switch ($i + 1) {
				case 1:
					$medal = Icons::MEDAL1 . ' ';
					break;
				case 2:
					$medal = Icons::MEDAL2 . ' ';
					break;
				case 3:
					$medal = Icons::MEDAL3 . ' ';
					break;
				default:
					$medal = '';
					break;
			}
			$message .= sprintf('%s%s %s WUs: %s, %s Credit: %s',
					$medal,
					self::formatUserLink($donor->name),
					Icons::STATS_WU,
					Utils::numberFormat($donor->wus),
					Icons::STATS_CREDIT,
					Utils::numberFormat($donor->credit)
				) . PHP_EOL;
		}
		return $message;
	}
}