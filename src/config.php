<?php
/**
 * Convert all errors to exceptions
 */
error_reporting(E_ALL);
set_error_handler('exceptions_error_handler');

function exceptions_error_handler($severity, $message, $filename, $lineno) {
	if (error_reporting() == 0) {
		return;
	}
	if (error_reporting() & $severity) {
		throw new ErrorException($message, 0, $severity, $filename, $lineno);
	}
}

DEFINE('FOLDING_STATS_URL', 'https://stats.foldingathome.org');
DEFINE('FOLDING_STATS_TIMEOUT', 5);

DEFINE('ERROR_CODE', 1);
DEFINE('LOG_ID', rand(10000, 99999));

DEFINE('LOG_FOLDER', __DIR__ . '/../data/log/');
DEFINE('DATE_FORMAT', 'Y-m-d');
DEFINE('TIME_FORMAT', 'H:i:s');

// @TODO in case of error, show some info about renaming config.local.example.php to config.local.php
require_once __DIR__ . '/../data/config.local.php';

require_once __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/functions.php';
include __DIR__ . '/Logs.php';
include __DIR__ . '/Utils.php';
include __DIR__ . '/Icons.php';
Logs::write('Request: ' . ($_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] . ' - ' : '') . $_SERVER['REQUEST_URI']);
