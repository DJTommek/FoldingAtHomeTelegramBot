<?php

namespace FoldingAtHome;

use DateTime;
use Exception;

class Team
{
	public $id; // in API it is property "team"

	private $wus;
	private $last;
	private $uid;
	private $active50;
	private $active7;
	private $credit;
	private $name;

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
	public function __construct(int $id, int $wus, DateTime $last, int $uid, int $active50, int $active7, int $credit, string $name) {
		$this->id = $id;
		$this->wus = $wus;
		$this->last = $last;
		$this->uid = $uid;
		$this->active50 = $active50;
		$this->active7 = $active7;
		$this->credit = $credit;
		$this->name = $name;
	}

	/**
	 * @param $json
	 * @return Team
	 * @throws Exception
	 */
	public static function createFromJson($json) {
		$last = new DateTime($json->last, new \DateTimeZone('UTC'));
		return new Team($json->team, $json->wus, $last, $json->uid, $json->active_50, $json->active_7, $json->credit, $json->name);
	}

	public function __get($name) {
		return $this->{$name};
	}
}