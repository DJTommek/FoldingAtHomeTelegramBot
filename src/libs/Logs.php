<?php

/**
 * Allow getting logs only to specific team(s)
 */
class Logs
{

	public static function write(string $text) {
		$date = date('Y-m-d');
		$time = date('H:i:s');
		$prefix = "[$date $time]";
		if (defined('LOG_ID')) {
			$prefix .= '[' . LOG_ID . ']';
		}
		file_put_contents(LOG_FOLDER . $date . '.log', $prefix . ' ' . $text . "\n", FILE_APPEND);
	}

	public static function getLogList() {

		$files = glob(LOG_FOLDER . '*.log');
		array_walk($files, ['self', 'cleanPath']);
		return $files;
	}

	public static function readLog(string $fileName): array {
		if (isset($fileName) && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}\.log$/", $fileName)) {
			$fileContent = file_get_contents(LOG_FOLDER . $fileName);
			$lines = explode(PHP_EOL, $fileContent);
			array_reverse($lines);
			return $lines;
		}
		return [];
	}

	private static function cleanPath(&$path, $key) {
		$path = basename($path);
	}

}
