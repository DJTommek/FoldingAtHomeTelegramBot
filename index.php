<?php
declare(strict_types=1);
$statuses = [];
require_once __DIR__ . '/src/config.php';
try {
	$db = Factory::get_database();
	$statuses['Database connection'] = 'connected';
	try {
		$tabledata = $db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME LIKE ?", DB_NAME, 'fahtb_%')->fetchAll();
		dd($tabledata, false);
		$statuses['Database installed tables'] = count($tabledata) . '/@TODO';
	} catch (Exception $exception) {
		$statuses['Database installed'] = 'error: ' . $exception->getMessage();
		dd($exception);
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
?>


