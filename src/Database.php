<?php

class Database
{
	/**
	 * @var PDO to database
	 */
	private $db;

	public function __construct($db_server, $db_schema, $db_user, $db_pass, $db_charset = 'utf8mb4') {
		$dsn = 'mysql:host=' . $db_server . ';dbname=' . $db_schema . ';charset=' . $db_charset;
		$this->db = new PDO($dsn, $db_user, $db_pass);
		$this->db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function getLink(): PDO {
		return $this->db;
	}

	public function query(string $query, ...$params) {
		$sql = $this->db->prepare($query);
		$sql->execute($params);
		return $sql;
	}

	public function registerUser(int $telegramId, ?string $telegramUsername = null) {
		$this->query('INSERT INTO fahtb_user (user_telegram_id, user_telegram_name, user_folding_name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE user_telegram_name = ?', $telegramId, $telegramUsername, $telegramUsername, $telegramUsername);
		return $this->getUser($telegramId);
	}

	public function updateUser(int $telegramId, ?string $telegramUsername = null, ?int $foldingId = null, ?string $foldingName = null, ?int $teamId = null, ?string $teamName = null) {
		$query = 'UPDATE fahtb_user SET ';
		$queries = [];
		$params = [];
		if ($telegramUsername) {
			$queries[] = 'user_telegram_name = ?';
			$params[] = $telegramUsername;
		}
		if ($foldingId) {
			$queries[] = 'user_folding_id = ?';
			$params[] = $foldingId;
		}
		if ($foldingName) {
			$queries[] = 'user_folding_name = ?';
			$params[] = $foldingName;
		}
		if ($teamId) {
			$queries[] = 'user_folding_team_id = ?';
			$params[] = $teamId;
		}
		if ($teamName) {
			$queries[] = 'user_folding_team_name = ?';
			$params[] = $teamName;
		}
		if (count($params) > 0) {
			$query .= join($queries, ', ') . ' WHERE user_telegram_id = ?';
			$params[] = $telegramId;
			call_user_func_array([$this, 'query'], array_merge([$query], $params));
		}
		return $this->getUser($telegramId);
	}


	public function getUser(int $telegramId) {
		return $this->query('SELECT * FROM fahtb_user WHERE user_telegram_id = ?', $telegramId)->fetchAll()[0];

	}
}
