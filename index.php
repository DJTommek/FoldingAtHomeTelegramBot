<?php
declare(strict_types=1);
$statuses = [];
require_once __DIR__ . '/src/config.php';
try {
	$db = Factory::get_database();
	$statuses['Database connection'] = 'connected';
	try {
		$tabledata = $db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME LIKE ?", DB_NAME, 'fahtb_%')->fetchAll();
		$totalTables = 1;
		if (count($tabledata) === $totalTables) {
			$statuses['Database installed tables'] = sprintf('all (%d)', $totalTables);
		} else {
			$statuses['Database installed tables'] = sprintf('error: installed only %d of %d', count($tabledata), $totalTables);
		}
	} catch (Exception $exception) {
		$statuses['Database installed'] = 'error: ' . $exception->getMessage();
	}
} catch (Exception $exception) {
	$statuses['Database connection'] = 'error: ' . $exception->getMessage();
}
?>
	<h1>FoldingAtHome Telegram bot</h1>
	<p>
		Index file for bot.
	</p>
	<ul>
		<li><a href="install.php">Install bot</a></li>
	</ul>
	<h2>Status</h2>
<?php
foreach ($statuses as $statusName => $statusText) {
	printf('<li><b>%s</b>: %s</li>', $statusName, $statusText);
}


