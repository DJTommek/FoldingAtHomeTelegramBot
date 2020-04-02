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

	public static function getTeamDataFromUserStats($stats) {
		try {
			return [$stats->teams[0]->id, $stats->teams[0]->name];
		} catch (Exception $exception) {
			return [0, 0];
		}

		// @TODO It seems, that API is always returning at least one team, even if user is not in any team (in that case it is team ID 0 with name "Default (No team specified)". Needs testing.
		$foldingTeamId = null;
		$foldingTeamName = null;
		if (count($stats->teams) > 0) {
			$foldingTeamId = $stats->teams[0]->team;
			$foldingTeamName = $stats->teams[0]->name;
		}
		return [0, 0];
	}

	public static function formatUserStats(\FoldingAtHome\User $stats) {
		$message = sprintf('%s\'s folding stats from %s:', Folding::formatUserLink($stats->name), TELEGRAM_BOT_NICK) . PHP_EOL;
		$message .= sprintf('%s <b>Credit</b>: %s (%s %s of %s users)',
				Icons::STATS_CREDIT,
				Utils::numberFormat($stats->credit),
				Icons::STATS_CREDIT_RANK,
				$stats->rank > 0 ? Utils::numberFormat($stats->rank) : '?',
				Utils::numberFormat($stats->totalUsers)
			) . PHP_EOL;
		$message .= sprintf('%s <b>WUs</b>: %s (<a href="%s">certificate</a>)',
				Icons::STATS_WU,
				Utils::numberFormat($stats->wus),
				$stats->wusCert . '&cachebuster=' . $stats->last->getTimestamp()
			) . PHP_EOL;
		//		$lastWUDone = new \DateTime($stats->last); // @TODO add "ago". Note: datetime is probably UTC+0, not sure how about summer time
		$message .= sprintf('%s <b>Last WU done</b>: %s',
				Icons::STATS_WU_LAST_DONE, $stats->last->format(DATE_FORMAT . ' ' . TIME_FORMAT)
			) . PHP_EOL;
		$message .= sprintf('%s‍ <b>Active client(s)</b>: %s / %s (last week / 50 days)',
				Icons::STATS_ACTIVE_CLIENTS,
				Utils::numberFormat($stats->active7),
				Utils::numberFormat($stats->active50)
			) . PHP_EOL;

		$message .= PHP_EOL;

		$message .= sprintf('Loaded %s UTC',
				gmdate(DATE_FORMAT . ' ' . TIME_FORMAT)
			) . PHP_EOL;
		return $message;
	}

	public static function formatTeamStats(\FoldingAtHome\Team $stats) {
		$message = sprintf('<a href="%s">%s</a>\'s team folding stats:', self::getTeamUrl($stats->id), $stats->name) . PHP_EOL;
		$message .= sprintf('%s <b>Credit</b>: %s (%s %s of %s teams, %s %s / user)',
				Icons::STATS_CREDIT,
				Utils::numberFormat($stats->credit),
				Icons::STATS_CREDIT_RANK,
				Utils::numberFormat($stats->rank),
				Utils::numberFormat($stats->totalTeams),
				Icons::AVERAGE,
				Utils::numberFormat($stats->credit / count($stats->donors))
			) . PHP_EOL;
		$message .= sprintf('%s <b>WUs</b>: %s (%s %s / user) <a href="%s">Certificate</a>',
				Icons::STATS_WU,
				Utils::numberFormat($stats->wus),
				Icons::AVERAGE,
				Utils::numberFormat($stats->wus / count($stats->donors), 2),
				$stats->wusCert . '&cachebuster=' . $stats->last->getTimestamp()
			) . PHP_EOL;
//		$lastWUDone = new \DateTime($stats->last); // @TODO add "ago". Note: datetime is probably UTC+0, not sure how about summer time
		$message .= sprintf('%s <b>Last WU done</b>: %s', Icons::STATS_WU_LAST_DONE, $stats->last->format(DATE_FORMAT . ' ' . TIME_FORMAT)) . PHP_EOL;
		$message .= sprintf('%s‍ <b>Active client(s)</b>: %s (last 50 days, %s %s / user)',
				Icons::STATS_ACTIVE_CLIENTS,
				Utils::numberFormat($stats->active50),
				Icons::AVERAGE,
				Utils::numberFormat($stats->active50 / count($stats->donors), 2)
			) . PHP_EOL;

		// Show top x donors but only if at least two donors are available
		if (count($stats->donors) >= 2) {
			$message .= PHP_EOL;
			$message .= Folding::formatTeamStatsTop($stats->donors);
		}
		$message .= PHP_EOL;

		$message .= sprintf('Loaded %s UTC',
				gmdate(DATE_FORMAT . ' ' . TIME_FORMAT)
			) . PHP_EOL;
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