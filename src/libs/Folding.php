<?php

class Folding
{
	public static function getDonorUrl(string $donorIdentificator, bool $api = false): string {
		$baseUrl = \FoldingAtHome\Request::STATS_BASE_URL;
		if ($api === true) {
			$baseUrl .= '/api';
		}
		$baseUrl .= '/donor/' . $donorIdentificator;
		return $baseUrl;
	}

	public static function getTeamUrl($teamIdentificator, bool $api = false): string {
		$baseUrl = \FoldingAtHome\Request::STATS_BASE_URL;
		if ($api === true) {
			$baseUrl .= '/api';
		}
		$baseUrl .= '/team/' . $teamIdentificator;
		return $baseUrl;
	}

	public static function formatDonorStats(\FoldingAtHome\Donor $stats, ?\DateTimeZone $timezone = null) {
		$buttons = [];
		if (is_null($timezone)) {
			$timezone = new DateTimeZone('UTC');
		}

		$message = sprintf('%s\'s folding stats from %s:', Folding::formatDonorLink($stats->name), TELEGRAM_BOT_NICK) . PHP_EOL;
		$message .= sprintf('%s <b>Credit</b>: %s (%s %s of %s donors)',
				Icons::STATS_CREDIT,
				Utils::numberFormat($stats->credit),
				Icons::STATS_CREDIT_RANK,
				$stats->rank > 0 ? Utils::numberFormat($stats->rank) : '?',
				Utils::numberFormat($stats->totalUsers)
			) . PHP_EOL;
		$message .= sprintf('%s <b>WUs</b>: %s (<a href="%s">certificate</a>)',
				Icons::STATS_WU,
				Utils::numberFormat($stats->wus),
				$stats->wusCert . '&cachebuster=' . $stats->last->getTimestamp() // @TODO cachebuster should be last WU done or "today", depending what is newer
			) . PHP_EOL;
		//		$lastWUDone = new \DateTime($stats->last); // @TODO add "ago". Note: datetime is probably UTC+0, not sure how about summer time
		$message .= sprintf('%s <b>Last WU done</b>: %s %s',
				Icons::STATS_WU_LAST_DONE,
				$stats->last->setTimezone($timezone)->format(DATETIME_FORMAT),
				\Utils\Datetime::formatTimezoneOffset($stats->last)
			) . PHP_EOL;
		$message .= sprintf('%s‍ <b>Active client(s)</b>: %s / %s (last week / 50 days)',
				Icons::STATS_ACTIVE_CLIENTS,
				Utils::numberFormat($stats->active7),
				Utils::numberFormat($stats->active50)
			) . PHP_EOL;

		// Show top x teams
		if (count($stats->teams) >= 1) {
			$message .= PHP_EOL;
			[$formatDonorTeamsMessage, $buttons] = Folding::formatDonorTeams($stats);
			$message .= $formatDonorTeamsMessage;
		}
		$message .= PHP_EOL;

		$now = (new DateTime())->setTimezone($timezone);
		$message .= sprintf('Loaded %s %s',
				$now->format(DATETIME_FORMAT),
				\Utils\Datetime::formatTimezoneOffset($now)
			) . PHP_EOL;
		return [$message, $buttons];
	}

	/**
	 * @param \FoldingAtHome\OSStats[] $OSStats
	 * @param DateTimeZone|null $timezone
	 * @return array
	 */
	public static function formatOSStats(array $OSStats, ?\DateTimeZone $timezone = null) {
		$buttons = [];
		if (is_null($timezone)) {
			$timezone = new DateTimeZone('UTC');
		}

		$message = sprintf('OS statistics stats from %s:', TELEGRAM_BOT_NICK) . PHP_EOL;

		foreach ($OSStats as $stat) {
//			$message .= sprintf('<b>%s</b>: %d | %d | %d | %d | %d | %d |', $stat->os, $stat->AMDGPUs, $stat->NVidiaGPUs, $stat->CPUs, $stat->CPUCores, $stat->TFLOPS, $stat->TFLOPSx86) . PHP_EOL;
			$message .= sprintf('%s <b>%s</b>', Icons::FOLDING, $stat->os) . PHP_EOL;
			$message .= sprintf('<b>AMD GPUs</b>: %s', Utils::numberFormat($stat->AMDGPUs)) . PHP_EOL;
			$message .= sprintf('<b>NVidia GPUs</b>: %s', Utils::numberFormat($stat->NVIDIAGPUs)) . PHP_EOL;
			$message .= sprintf('<b>CPUs</b>: %s', Utils::numberFormat($stat->CPUs)) . PHP_EOL;
			$message .= sprintf('<b>CPU cores</b>: %s', Utils::numberFormat($stat->CPUCores)) . PHP_EOL;
			$message .= sprintf('<b>TFLOPS</b>: %s', Utils::numberFormat($stat->TFLOPS)) . PHP_EOL;
			$message .= sprintf('<b>x86 TFLOPS</b>: %s', Utils::numberFormat($stat->TFLOPSx86)) . PHP_EOL;
			$message .= PHP_EOL;
		}

		return [$message, $buttons];
	}

	public static function formatTeamStats(\FoldingAtHome\Team $stats, ?\DateTimeZone $timezone = null) {
		$buttons = [];
		if (is_null($timezone)) {
			$timezone = new DateTimeZone('UTC');
		}
		$buttons = [];
		$message = sprintf('<a href="%s">%s</a>\'s team folding stats from %s:', self::getTeamUrl($stats->id), $stats->name, TELEGRAM_BOT_NICK) . PHP_EOL;
		$message .= sprintf('%s <b>Credit</b>: %s (%s %s of %s teams, %s %s / donor)',
				Icons::STATS_CREDIT,
				Utils::numberFormat($stats->credit),
				Icons::STATS_CREDIT_RANK,
				Utils::numberFormat($stats->rank),
				Utils::numberFormat($stats->totalTeams),
				Icons::AVERAGE,
				Utils::numberFormat($stats->credit / count($stats->donors))
			) . PHP_EOL;
		$message .= sprintf('%s <b>WUs</b>: %s (%s %s / donor) <a href="%s">Certificate</a>',
				Icons::STATS_WU,
				Utils::numberFormat($stats->wus),
				Icons::AVERAGE,
				Utils::numberFormat($stats->wus / count($stats->donors), 2),
				$stats->wusCert . '&cachebuster=' . $stats->last->getTimestamp()
			) . PHP_EOL;
//		$lastWUDone = new \DateTime($stats->last); // @TODO add "ago". Note: datetime is probably UTC+0, not sure how about summer time
		$message .= sprintf('%s <b>Last WU done</b>: %s %s',
				Icons::STATS_WU_LAST_DONE,
				$stats->last->setTimezone($timezone)->format(DATETIME_FORMAT),
				\Utils\Datetime::formatTimezoneOffset($stats->last)
			) . PHP_EOL;
		$message .= sprintf('%s‍ <b>Active client(s)</b>: %s (last 50 days, %s %s / donor)',
				Icons::STATS_ACTIVE_CLIENTS,
				Utils::numberFormat($stats->active50),
				Icons::AVERAGE,
				Utils::numberFormat($stats->active50 / count($stats->donors), 2)
			) . PHP_EOL;

		// Show top x donors but only if at least two donors are available
		if (count($stats->donors) >= 2) {
			$message .= PHP_EOL;
			[$formatTeamDonorsMessage, $buttons] = Folding::formatTeamDonors($stats);
			$message .= $formatTeamDonorsMessage;
		}
		$message .= PHP_EOL;

		$now = (new DateTime())->setTimezone($timezone);
		$message .= sprintf('Loaded %s %s',
				$now->format(DATETIME_FORMAT),
				\Utils\Datetime::formatTimezoneOffset($now)
			) . PHP_EOL;
		return [$message, $buttons];
	}

	public static function formatDonorLink($donorIdentificator) {
		return sprintf('<a href="%s">%s</a>', self::getDonorUrl($donorIdentificator), $donorIdentificator);
	}

	public static function formatTeamLink($teamId, $teamName) {
		return sprintf('<a href="%s">%s</a>', self::getTeamUrl($teamId), $teamName);
	}

	public static function formatDonorTeams(FoldingAtHome\Donor $donorStats, $count = 5) {
		$showing = min(count($donorStats->teams), $count);
		if ($showing === 0) {
			return [null, null];
		}
		$buttons = [];
		$message = sprintf('%s <b>%d team(s) of %d total</b>:', Icons::STATS_TEAM_TOP, $showing, count($donorStats->teams)) . PHP_EOL;
		foreach ($donorStats->teams as $i => $team) {
			if ($i >= $showing) {
				break;
			}

			$wusPercent = ($team->wus / $donorStats->wus) * 100;
			$creditPercent = ($team->credit / $donorStats->credit) * 100;

			$message .= sprintf('%s %s WUs: %s (%s%%), %s Credit: %s (%s%%)',
					self::formatTeamLink($team->id, $team->name),
					Icons::STATS_WU,
					Utils::numberFormat($team->wus),
					Utils::numberFormat($wusPercent, self::getNumberOfDecimalPlaces($wusPercent)),
					Icons::STATS_CREDIT,
					Utils::numberFormat($team->credit),
					Utils::numberFormat($creditPercent, self::getNumberOfDecimalPlaces($creditPercent))
				) . PHP_EOL;
			$buttons[] = [
				[
					'text' => sprintf('%s %s', Icons::TEAM, $team->name),
					'callback_data' => sprintf('%s %d', TelegramWrapper\Command\Command::CMD_TEAM, $team->id),
				],
			];
		}
		return [$message, $buttons];
	}

	public static function formatTeamDonors($teamStats, $count = 5) {
		$buttons = [];
		$showing = min(count($teamStats->donors), $count);
		if ($showing === 0) {
			return [null, null];
		}
		$message = sprintf('%s <b>Top %d donors of %d members total</b>:', Icons::STATS_TEAM_TOP, $showing, count($teamStats->donors)) . PHP_EOL;
		foreach ($teamStats->donors as $i => $donor) {
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

			$wusPercent = ($donor->wus / $teamStats->wus) * 100;
			$creditPercent = ($donor->credit / $teamStats->credit) * 100;

			$message .= sprintf('%s%s %s WUs: %s (%s%%), %s Credit: %s (%s%%)',
					$medal,
					self::formatDonorLink($donor->name),
					Icons::STATS_WU,
					Utils::numberFormat($donor->wus),
					Utils::numberFormat($wusPercent, self::getNumberOfDecimalPlaces($wusPercent)),
					Icons::STATS_CREDIT,
					Utils::numberFormat($donor->credit),
					Utils::numberFormat($creditPercent, self::getNumberOfDecimalPlaces($creditPercent))
				) . PHP_EOL;
			$buttons[] = [
				[
					'text' => sprintf('%s %s', Icons::DONOR, $donor->name),
					'callback_data' => sprintf('%s %d', \TelegramWrapper\Command\Command::CMD_DONOR, $donor->id),
				],
			];
		}
		return [$message, $buttons];
	}

	private static function getNumberOfDecimalPlaces(float $number) {
		if ($number > 10) {
			return 0;
		}
		if ($number > 1) {
			return 1;
		}
		return 2;
	}
}