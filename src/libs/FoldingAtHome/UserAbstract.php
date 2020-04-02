<?php

namespace FoldingAtHome;

use DateTime;
use Exception;

class UserAbstract
{
	protected $id;

	protected $wus;
	protected $credit;
	protected $rank;
	protected $name;
//	protected$team;

	/**
	 * UserAbstract constructor.
	 *
	 * @param int $id
	 * @param int $wus
	 * @param int $credit
	 * @param string $name
	 * //     * @param int $team
	 * @param int|null $rank
	 */
	public function __construct(int $id, int $wus, int $credit, string $name, ?int $rank = null) {
		// @TODO what is "team" in API? Add support
		$this->id = $id;
		$this->wus = $wus;
		$this->credit = $credit;
		$this->rank = $rank;
		$this->name = $name;
//		$this->team = $team;
	}

	public function __get($name) {
		return $this->{$name};
	}
}