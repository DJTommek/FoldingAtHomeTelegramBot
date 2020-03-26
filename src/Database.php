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

	public function updateUser(int $telegramId, ?string $telegramUsername = null, ?string $foldingName = null, ?int $foldingId = null, ?int $teamId = null, ?int $teamName = null) {
		$this->query('UPDATE fahtb_user SET user_telegram_name = ?, user_folding_name = ?, user_folding_id = ? , user_folding_team_id = ?, user_folding_team_name = ? WHERE user_telegram_id = ?',
			$telegramUsername, $foldingName, $foldingId, $teamId, $teamName, $telegramId);
		return $this->getUser($telegramId);
	}


	public function getUser(int $telegramId) {
		return $this->query('SELECT * FROM fahtb_user WHERE user_telegram_id = ?', $telegramId)->fetchAll()[0];

	}
}
