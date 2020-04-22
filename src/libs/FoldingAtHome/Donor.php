<?php

namespace FoldingAtHome;

use Exception;

class Donor extends DonorAbstract
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
	 * Donor constructor.
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
	 * @param \DateTime $last
	 * @param string $name
	 * @param DonorTeam[] $teams
	 * @throws Exceptions\GeneralException
	 */
	public function __construct(int $id, int $wus, int $credit, int $rank, int $totalUsers, int $active7, int $active50, string $wusCert, string $creditCert, string $path, \Datetime $last, string $name, array $teams) {
		parent::__construct($id, $wus, $credit, $name, $rank);
		foreach ($teams as $team) {
			if ($team instanceof DonorTeam === false) {
				throw new Exceptions\GeneralException('Some parameter(s) of $teams is not instance of DonorTeam'); // @TODO text "DonorTeam" should be dynamically defined from class name. Also add what instance it is
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
	 * @return Donor
	 * @throws Exceptions\GeneralException
	 * @throws Exception
	 */
	public static function createFromJson($json) {
		$last = new \DateTime($json->last, new \DateTimeZone('UTC'));
		$teams = [];
		foreach ($json->teams as $team) {
			$teams[] = DonorTeam::createFromJson($team);
		}
		return new Donor($json->id, $json->wus, $json->credit, $json->rank, $json->total_users, $json->active_7, $json->active_50, $json->wus_cert, $json->credit_cert, $json->path, $last, $json->name, $teams);
	}

	public function __get($name) {
		return $this->{$name};
	}
}