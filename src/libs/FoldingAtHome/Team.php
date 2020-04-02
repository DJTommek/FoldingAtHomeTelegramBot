<?php

namespace FoldingAtHome;

use DateTime;
use Exception;

class Team extends TeamAbstract
{
	private $url;
	private $logo;
	private $wusCert;
	private $creditCert;
	private $rank;
	private $totalTeams;
	private $path;

	/**
	 * Team constructor.
	 *
	 * @param int $id
	 * @param int $wus
	 * @param DateTime $last
	 * @param int $active50
	 * @param int $credit
	 * @param string $name
	 * @param $url
	 * @param $logo
	 * @param $wusCert
	 * @param $creditCert
	 * @param $rank
	 * @param $totalTeams
	 * @param $path
	 */
	public function __construct(int $id, int $wus, DateTime $last, int $active50, int $credit, string $name, string $url, string $logo, string $wusCert, string $creditCert, int $rank, int $totalTeams, string $path) {
		parent::__construct($id, $wus, $last, $active50, $credit, $name);
		$this->url = $url;
		$this->logo = $logo;
		$this->wusCert = $wusCert;
		$this->creditCert = $creditCert;
		$this->rank = $rank;
		$this->totalTeams = $totalTeams;
		$this->path = $path;
	}

	/**
	 * @param $json
	 * @return Team
	 * @throws Exception
	 */
	public static function createFromJson($json) {
		$last = new DateTime($json->last, new \DateTimeZone('UTC'));
		return new Team($json->team, $json->wus, $last, $json->active_50, $json->credit, $json->name, $json->url, $json->logo, $json->wus_cert, $json->credit_cert, $json->rank, $json->total_teams, $json->path);
	}

	public function __get($name) {
		return $this->{$name};
	}
}