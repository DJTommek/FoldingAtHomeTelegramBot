<?php

namespace FoldingAtHome;

class RequestUser extends Request
{
	public $id;

	/**
	 * RequestUser constructor.
	 *
	 * @param $userIdentificator
	 * @throws Exceptions\BadRequestException
	 */
	public function __construct($userIdentificator) {
		$paramType = gettype($userIdentificator);
		if ($paramType === 'string' || $paramType === 'int') {
			$this->id = $userIdentificator;
		} else {
			throw new Exceptions\BadRequestException('Invalid parameter, $userIdentificator has to be string for donor name or int for donor ID.');
		}
	}

	/**
	 * @param string $userId
	 * @param bool $api
	 * @return string
	 */
	public function getUrl(string $userId, bool $api = false): string {
		$baseUrl = self::STATS_BASE_URL;
		if ($api === true) {
			$baseUrl .= '/api';
		}
		$baseUrl .= '/donor/' . $userId;
		return $baseUrl;
	}

	/**
	 * Download stats from FoldingAtHome API
	 *
	 * @param array $curlOpts
	 * @return User
	 * @throws Exceptions\ApiErrorException
	 * @throws Exceptions\ApiTimeoutException
	 * @throws Exceptions\BadRequestException
	 * @throws Exceptions\BadResponseException
	 * @throws Exceptions\GeneralException
	 * @throws Exceptions\NotFoundException
	 */
	public function load(array $curlOpts = []) {
		$apiUrl = $this->getUrl($this->id, true);
		$jsonResponse = $this->fileGetContent($apiUrl, $curlOpts + [
				CURLOPT_CONNECTTIMEOUT => self::TIMEOUT,
				CURLOPT_TIMEOUT => self::TIMEOUT,
		]); // override values from outside
		return User::createFromJson($jsonResponse);
	}
}