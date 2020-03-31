<?php
declare(strict_types=1);

require_once __DIR__ . '/src/config.php';

$files = Logs::getLogList();

?>
	<form method="GET">
		<select name="log">
			<?php
			foreach ($files as $file) {
				echo '<option value="' . $file  . '" ' . ($_GET['log'] === $file ? 'selected' : '') . '>' . $file . '</option>';
			}
			?>
		</select>
		<button type="submit">Open log</button>
	</form>
<?php
if (isset($_GET['log'])) {
	$logLines = Logs::readLog($_GET['log']);
	if (empty($logLines)) {
		echo '<p>Log file is empty</p>';
	} else {
		echo '<pre>';
		foreach ($logLines as $logLine) {
			echo $logLine . PHP_EOL;
		}
		echo '</pre>';
	}
}
