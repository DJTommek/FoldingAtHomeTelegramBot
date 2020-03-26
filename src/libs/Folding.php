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
}