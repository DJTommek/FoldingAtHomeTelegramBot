<?php

class User
{
	private $db;

	private $id;
	private $telegramId;
	private $telegramUsername;
	private $foldingId;
	private $foldingName;
	private $foldingTeamId;
	private $foldingTeamName;
	private $settings = [];

	/**
	 * User constructor.
	 *
	 * @param int $telegramId
	 * @param string|null $telegramUsername
	 */
	public function __construct(int $telegramId, ?string $telegramUsername = null) {
		$this->telegramId = $telegramId;
		$this->telegramUsername = $telegramUsername;
		$this->db = Factory::get_database();
		$userData = $this->register($telegramId, $telegramUsername);
		$this->updateCachedData($userData);
	}

	private function updateCachedData($newUserData) {
		$this->id = $newUserData['user_id'];
		$this->telegramId = $newUserData['user_telegram_id'];
		$this->telegramUsername = $newUserData['user_telegram_name'];
		$this->foldingId = $newUserData['user_folding_id'];
		$this->foldingName = $newUserData['user_folding_name'];
		$this->foldingTeamId = $newUserData['user_folding_team_id'];
		$this->foldingTeamName = $newUserData['user_folding_team_name'];
		$this->settings['timezone'] = new \DateTimeZone($newUserData['user_settings_timezone']);
	}

	public function register(int $telegramId, ?string $telegramUsername = null) {
		$this->db->query('INSERT INTO fahtb_user (user_telegram_id, user_telegram_name, user_folding_name, user_last_update) VALUES (?, ?, ?, UTC_TIMESTAMP( )) 
			ON DUPLICATE KEY UPDATE user_telegram_name = ?, user_last_update = UTC_TIMESTAMP( )',
			$telegramId, $telegramUsername, $telegramUsername ?? \FoldingAtHome\DonorAbstract::DEFAULT_NAME, $telegramUsername
		);
		return $this->load();
	}

	public function load() {
		return $this->db->query('SELECT * FROM fahtb_user WHERE user_telegram_id = ?', $this->telegramId)->fetchAll()[0];
	}

	public function updateTeam(int $foldingTeamId, string $foldingTeamName) {
		return $this->update(null, null, null, $foldingTeamId, $foldingTeamName);
	}

	public function updateTimezone(DateTimeZone $timeZone) {
		return $this->update(null, null, null, null, null, $timeZone);
	}

	public function update(?string $telegramUsername = null, ?int $foldingId = null, ?string $foldingName = null, ?int $teamId = null, ?string $teamName = null, ?DateTimeZone $timezone = null) {
		$queries = [];
		$params = [];
		if (is_string($telegramUsername)) {
			$queries[] = 'user_telegram_name = ?';
			$params[] = $telegramUsername;
		}
		if (is_int($foldingId)) {
			$queries[] = 'user_folding_id = ?';
			$params[] = $foldingId;
		}
		if (is_string($foldingName)) {
			$queries[] = 'user_folding_name = ?';
			$params[] = $foldingName;
		}
		if (is_int($teamId)) {
			$queries[] = 'user_folding_team_id = ?';
			$params[] = $teamId;
		}
		if (is_string($teamName)) {
			$queries[] = 'user_folding_team_name = ?';
			$params[] = $teamName;
		}
		if ($timezone) {
			$queries[] = 'user_settings_timezone = ?';
			$params[] = $timezone->getName();
		}
		if (count($params) > 0) {
			$query = sprintf('UPDATE fahtb_user SET %s WHERE user_telegram_id = ?', join($queries, ', '));

			$params[] = $this->telegramId;
			call_user_func_array([$this->db, 'query'], array_merge([$query], $params));
			$newData = $this->load();
			$this->updateCachedData($newData);
		} else {
			return $this->get();
		}
	}

	public function getUrl() {
		return Folding::getDonorUrl($this->foldingName);
	}

	public function get() {
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getTelegramId() {
		return $this->telegramId;
	}

	/**
	 * @return mixed
	 */
	public function getTelegramUsername() {
		return $this->telegramUsername;
	}

	/**
	 * @return mixed
	 */
	public function getFoldingId() {
		return $this->foldingId;
	}

	/**
	 * @return mixed
	 */
	public function getFoldingName() {
		return $this->foldingName;
	}

	/**
	 * @return mixed
	 */
	public function getFoldingTeamId() {
		return $this->foldingTeamId;
	}

	/**
	 * @return mixed
	 */
	public function getFoldingTeamName() {
		return $this->foldingTeamName;
	}

	/**
	 * @return \DateTimeZone
	 */
	public function getTimezone() {
		return $this->settings['timezone'];
	}

	/**
	 * @param string|null $name
	 * @return array|mixed
	 */
	public function getSettings(?string $name = null) {
		if (is_null($name)) {
			return $this->settings;
		} else {
			return $this->settings[$name];
		}
	}

}