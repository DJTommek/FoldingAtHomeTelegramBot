<?php

namespace FoldingAtHome;

use DateTime;
use Exception;

class TeamAbstract
{
	protected $id; // in API it is property "team"

	protected $wus;
	protected $last;
	protected $active50;
	protected $credit;
	protected $name;

	/**
	 * TeamAbstract constructor.
	 *
	 * @param int $id
	 * @param int $wus
	 * @param DateTime|null $last
	 * @param int $active50
	 * @param int $credit
	 * @param string $name
	 */
	public function __construct(int $id, int $wus, ?DateTime $last, int $active50, int $credit, string $name) {
		$this->id = $id;
		$this->wus = $wus;
		$this->last = $last;
		$this->active50 = $active50;
		$this->credit = $credit;
		$this->name = $name;
	}

	public function __get($name) {
		return $this->{$name};
	}
}