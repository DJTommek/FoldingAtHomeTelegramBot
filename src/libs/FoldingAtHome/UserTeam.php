<?php

namespace FoldingAtHome;

use DateTime;
use Exception;

class UserTeam extends TeamAbstract
{
	protected $uid;
	protected $active7;

	/**
	 * Team constructor.
	 *
	 * @param $id
	 * @param $wus
	 * @param $last
	 * @param $uid
	 * @param $active50
	 * @param $active7
	 * @param $credit
	 * @param $name
	 */
	public function __construct(int $id, int $wus, DateTime $last, int $active50, int $active7, int $credit, string $name, int $uid) {
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
		$last = new DateTime($json->last, new \DateTimeZone('UTC'));
		return new UserTeam($json->team, $json->wus, $last, $json->active_50, $json->active_7, $json->credit, $json->name, $json->uid);
	}

	public function __get($name) {
		return $this->{$name};
	}
}