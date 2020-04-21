<?php

namespace FoldingAtHome;

class RequestDonor extends Request
{
	public $id;

	/**
	 * RequestDonor constructor.
	 *
	 * @param $donorIdentificator
	 * @throws Exceptions\BadRequestException
	 */
	public function __construct($donorIdentificator) {
		$paramType = gettype($donorIdentificator);
		if ($paramType === 'string' || $paramType === 'int') {
			$this->id = $donorIdentificator;
		} else {
			throw new Exceptions\BadRequestException('Invalid parameter, $donorIdentificator has to be string for donor name or int for donor ID.'); // @TODO "$donorIdentificator" in text should be loaded dynamically in case, that variable gets renamed
		}
	}

	/**
	 * @param string $donorId
	 * @param bool $api
	 * @return string
	 */
	public function getUrl(string $donorId, bool $api = false): string {
		$baseUrl = self::STATS_BASE_URL;
		if ($api === true) {
			$baseUrl .= '/api';
		}
		$baseUrl .= '/donor/' . $donorId;
		return $baseUrl;
	}

	/**
	 * Download stats from FoldingAtHome API
	 *
	 * @param array $curlOpts
	 * @return Donor
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
		return Donor::createFromJson($jsonResponse);
	}
}