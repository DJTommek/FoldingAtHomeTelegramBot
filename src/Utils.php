<?php

class Utils
{
	static function sToHuman($seconds) {
		$dtF = new \DateTime('@0');
		$dtT = new \DateTime("@$seconds");
		return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
	}

	static function numberFormat(int $number, int $decimals = 0): string {
		return \number_format($number, $decimals, '.', ' ');
	}

	static function requestJson(string $url, int $timeout = FOLDING_STATS_TIMEOUT) {
		$stream_context = stream_context_create([
			'http' => [
				'timeout' => $timeout,
			]
		]);
		$response = @file_get_contents($url, false, $stream_context);
		if ($response === null) {
			return null;
		}
		return json_decode($response);
	}
}