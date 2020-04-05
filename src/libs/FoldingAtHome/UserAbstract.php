<?php

namespace FoldingAtHome;

use DateTime;
use Exception;

class UserAbstract
{
	const DEFAULT_NAME = 'Anonymous';
	const DEFAULT_ID = 1437;

	protected $id;

	protected $wus;
	protected $credit;
	protected $rank;
	protected $name;

	/**
	 * UserAbstract constructor.
	 *
	 * @param int $id
	 * @param int $wus
	 * @param int $credit
	 * @param string $name
	 * @param int|null $rank
	 */
	public function __construct(int $id, int $wus, int $credit, string $name, ?int $rank = null) {
		$this->id = $id;
		$this->wus = $wus;
		$this->credit = $credit;
		$this->rank = $rank;
		$this->name = $name;
	}

	public function __get($name) {
		return $this->{$name};
	}
}