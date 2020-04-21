<?php

namespace FoldingAtHome;

class RequestTeam extends Request
{
	public $id;

	/**
	 * RequestTeam constructor.
	 *
	 * @param int $teamId
	 */
	public function __construct(int $teamId) {
		$this->id = $teamId;
	}

	public function getUrl(string $teamIdentificator, bool $api = false): string {
		$baseUrl = self::STATS_BASE_URL;
		if ($api === true) {
			$baseUrl .= '/api';
		}
		$baseUrl .= '/team/' . $teamIdentificator;
		return $baseUrl;
	}

	/**
	 * @param array $curlOpts
	 * @return Team
	 * @throws Exceptions\ApiErrorException
	 * @throws Exceptions\ApiTimeoutException
	 * @throws Exceptions\BadRequestException
	 * @throws Exceptions\BadResponseException
	 * @throws Exceptions\NotFoundException
	 * @throws \Exception
	 */
	public function load(array $curlOpts = []) {
		$apiUrl = $this->getUrl($this->id, true);
		$jsonResponse = $this->fileGetContent($apiUrl, $curlOpts + [
				CURLOPT_CONNECTTIMEOUT => self::TIMEOUT,
				CURLOPT_TIMEOUT => self::TIMEOUT,
		]); // override values from outside
		return Team::createFromJson($jsonResponse);
	}
}