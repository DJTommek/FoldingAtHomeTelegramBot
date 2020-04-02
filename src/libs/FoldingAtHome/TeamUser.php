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
	 * @return TeamUser
	 */
	public static function createFromJson($json) {
		return new TeamUser($json->id, $json->wus, $json->credit, $json->name, $json->rank ?? null);
	}
}