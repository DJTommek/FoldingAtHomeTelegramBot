<?php
declare(strict_types=1);
echo '<pre>';

require_once __DIR__ . '/src/config.php';

$db = Factory::get_database();
$db->query('CREATE TABLE user');
