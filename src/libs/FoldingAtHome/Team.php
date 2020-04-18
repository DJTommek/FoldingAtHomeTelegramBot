<?php

namespace FoldingAtHome;

use DateTime;
use Exception;

class Team extends TeamAbstract
{
	protected $url;
	protected $logo;
	protected $wusCert;
	protected $creditCert;
	protected $rank;
	protected $totalTeams;
	protected $path;
	protected $donors = [];

	/**
	 * Team constructor.
	 *
	 * @param int $id
	 * @param int $wus
	 * @param DateTime $last
	 * @param int $active50
	 * @param int $credit
	 * @param string $name
	 * @param string|null $url
	 * @param string|null $logo
	 * @param string $wusCert
	 * @param string $creditCert
	 * @param int $rank
	 * @param int $totalTeams
	 * @param string $path
	 * @param User[] $donors
	 * @throws Exceptions\GeneralException
	 */
	public function __construct(int $id, int $wus, DateTime $last, int $active50, int $credit, string $name, ?string $url, ?string $logo, string $wusCert, string $creditCert, int $rank, int $totalTeams, string $path, array $donors) {
		parent::__construct($id, $wus, $last, $active50, $credit, $name);
		foreach ($donors as $donor) {
			if ($donor instanceof TeamUser === false) {
				throw new Exceptions\GeneralException('Some parameter(s) of $donors is not instance of TeamUser');
			}
		}
		$this->url = $url;
		$this->logo = $logo;
		$this->wusCert = $wusCert;
		$this->creditCert = $creditCert;
		$this->rank = $rank;
		$this->totalTeams = $totalTeams;
		$this->path = $path;
		$this->donors = $donors;
	}

	/**
	 * @param $json
	 * @return Team
	 * @throws Exception
	 */
	public static function createFromJson($json) {
		$last = new DateTime($json->last, new \DateTimeZone('UTC'));
		$donors = [];
		foreach ($json->donors as $donor) {
			$donors[] = TeamUser::createFromJson($donor);
		}
		return new Team($json->team, $json->wus, $last, $json->active_50, $json->credit, $json->name, $json->url, $json->logo, $json->wus_cert, $json->credit_cert, $json->rank, $json->total_teams, $json->path, $donors);
	}
}