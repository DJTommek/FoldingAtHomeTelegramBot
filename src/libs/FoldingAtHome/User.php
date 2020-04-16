<?php

namespace FoldingAtHome;

use DateTime;
use Exception;

class User extends UserAbstract
{
	protected $id;


	protected $wus;
	protected $credit;
	protected $rank;
	protected $totalUsers;
	protected $active7;
	protected $active50;
	protected $wusCert;
	protected $creditCert;
	protected $path;
	protected $last;
	protected $name;
	protected $teams = [];

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
	 * @param UserTeam[] $teams
	 * @throws Exceptions\GeneralException
	 */
	public function __construct(int $id, int $wus, int $credit, int $rank, int $totalUsers, int $active7, int $active50, string $wusCert, string $creditCert, string $path, DateTime $last, string $name, array $teams) {
		parent::__construct($id, $wus, $credit, $name, $rank);
		foreach ($teams as $team) {
			if ($team instanceof UserTeam === false) {
				throw new Exceptions\GeneralException('Some parameter(s) of $teams is not instance of UserTeam');
			}
		}
		$this->totalUsers = $totalUsers;
		$this->active7 = $active7;
		$this->active50 = $active50;
		$this->wusCert = $wusCert;
		$this->creditCert = $creditCert;
		$this->path = $path;
		$this->last = $last;
		$this->teams = $teams;
	}

	/**
	 * Dynamically create object from JSON downloaded from API
	 *
	 * @param $json
	 * @return User
	 * @throws Exceptions\GeneralException
	 * @throws Exception
	 */
	public static function createFromJson($json) {
		$last = new DateTime($json->last, new \DateTimeZone('UTC'));
		$teams = [];
		foreach ($json->teams as $team) {
			$teams[] = UserTeam::createFromJson($team);
		}
		return new User($json->id, $json->wus, $json->credit, $json->rank, $json->total_users, $json->active_7, $json->active_50, $json->wus_cert, $json->credit_cert, $json->path, $last, $json->name, $teams);
	}

	public function __get($name) {
		return $this->{$name};
	}
}