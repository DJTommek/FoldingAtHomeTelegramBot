<?php

namespace FoldingAtHome;

use Cassandra\Date;
use DateTime;
use Exception;

class User
{
	private $id;

	private $wus;
	private $credit;
	private $rank;
	private $totalUsers;
	private $active7;
	private $active50;
	private $wusCert;
	private $creditCert;
	private $path;
	private $last;
	private $name;
	private $teams = [];

	/**
	 * User constructor.
	 *
	 * @param int $id
	 * @param int $wus
	 * @param int $credit
	 * @param int $rank
	 * @param int $totalUsers
	 * @param int $active7
	 * @param int $active50
	 * @param string $wusCert
	 * @param string $creditCert
	 * @param string $path
	 * @param DateTime $last
	 * @param string $name
	 * @param Team[] $teams
	 * @throws Exceptions\GeneralException
	 */
	public function __construct(int $id, int $wus, int $credit, int $rank, int $totalUsers, int $active7, int $active50, string $wusCert, string $creditCert, string $path, DateTime $last, string $name, array $teams) {
		foreach ($teams as $team) {
			if ($team instanceof Team === false) {
				throw new Exceptions\GeneralException('Parameter $team is not instance of Team');
			}
		}
		$this->id = $id;
		$this->wus = $wus;
		$this->credit = $credit;
		$this->rank = $rank;
		$this->totalUsers = $totalUsers;
		$this->active7 = $active7;
		$this->active50 = $active50;
		$this->wusCert = $wusCert;
		$this->creditCert = $creditCert;
		$this->path = $path;
		$this->last = $last;
		$this->name = $name;
		$this->teams = $teams;
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
		foreach ($json->teams as $team) {
			$teams[] = Team::createFromJson($team);
		}
		return new User($json->id, $json->wus, $json->credit, $json->rank, $json->total_users, $json->active_7, $json->active_50, $json->wus_cert, $json->credit_cert, $json->path, $last, $json->name, $teams);
	}

	public function __get($name) {
		return $this->{$name};
	}
}