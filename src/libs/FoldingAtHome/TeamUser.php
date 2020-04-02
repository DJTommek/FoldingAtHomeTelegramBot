<?php

namespace FoldingAtHome;

use DateTime;
use Exception;

class TeamUser extends UserAbstract
{
	/**
	 * TeamUser constructor.
	 *
	 * @param int $id
	 * @param int $wus
	 * @param int $credit
	 * @param string $name
	 * @param int|null $rank
	 */
	public function __construct(int $id, int $wus, int $credit, string $name, ?int $rank = null) {
		parent::__construct($id, $wus, $credit, $name, $rank);
	}

	/**
	 * @param $json
	 * @return User
	 * @throws Exceptions\GeneralException
	 * @throws Exception
	 */
	public static function createFromJson($json) {
		$last = new DateTime($json->last, new \DateTimeZone('UTC'));
		$teams = [];
		return new TeamUser($json->id, $json->wus, $json->credit, $json->rank, $json->total_users, $json->active_7, $json->active_50, $json->wus_cert, $json->credit_cert, $json->path, $last, $json->name, $teams);
	}

	public function __get($name) {
		return $this->{$name};
	}
}