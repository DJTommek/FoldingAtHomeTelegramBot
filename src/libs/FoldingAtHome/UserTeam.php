<?php

namespace FoldingAtHome;

use DateTime;
use Exception;

class UserTeam extends TeamAbstract
{
	protected $uid;
	protected $active7;

	/**
	 * UserTeam constructor.
	 *
	 * @param int $id
	 * @param int $wus
	 * @param DateTime|null $last
	 * @param int $active50
	 * @param int $active7
	 * @param int $credit
	 * @param string $name
	 * @param int $uid
	 */
	public function __construct(int $id, int $wus, ?DateTime $last, int $active50, int $active7, int $credit, string $name, int $uid) {
		parent::__construct($id, $wus, $last, $active50, $credit, $name);
		$this->uid = $uid;
		$this->active7 = $active7;
	}

	/**
	 * @param $json
	 * @return UserTeam
	 * @throws Exception
	 */
	public static function createFromJson($json) {
		$last = isset($json->last) ? new DateTime($json->last, new \DateTimeZone('UTC')) : null;
		return new UserTeam($json->team, $json->wus, $last, $json->active_50, $json->active_7, $json->credit, $json->name, $json->uid);
	}

	public function __get($name) {
		return $this->{$name};
	}
}