<?php

namespace FoldingAtHome;

abstract class Request
{
	const STATS_BASE_URL = 'https://stats.foldingathome.org';

	/**
	 * API is very slow if request is not cached.
	 * Recommended usage is make request and if API doesn't respond in short time, wait a while (might take up to minute) and repeat request again.
	 * Now it should be cached on their servers and response should be quick.
	 */
	const TIMEOUT = 3;

	const FOLDING_ERROR_NOT_FOUND = 'Not found';

	/**
	 * @param string $url
	 * @param array $curlOpts
	 * @return mixed
	 * @throws Exceptions\ApiErrorException
	 * @throws Exceptions\BadResponseException
	 * @throws Exceptions\ApiTimeoutException
	 * @throws Exceptions\NotFoundException
	 * @throws Exceptions\BadRequestException
	 */
	public function fileGetContent(string $url, array $curlOpts = []) {
		$curl = curl_init($url);
		$curlOpts[CURLOPT_RETURNTRANSFER] = true;
		$curlOpts[CURLOPT_HEADER] = true;
		curl_setopt_array($curl, $curlOpts);
		$curlResponse = curl_exec($curl);
		if ($curlResponse === false) { // @TODO translate to CURLE_something error https://www.php.net/manual/en/function.curl-errno.php
			if (curl_errno($curl) === 28) {
				throw new Exceptions\ApiTimeoutException('API request timeouted.');
			} else {
				throw new Exceptions\BadRequestException('API request CURL error ' . curl_errno($curl));
			}
		}
		list($header, $body) = explode("\r\n\r\n", $curlResponse, 2);
		if (!$body) {
			$responseCode = trim(explode(PHP_EOL, $header)[0]);
			throw new Exceptions\BadResponseException('Bad API response from "' . $url . '": "' . $responseCode . '".');
		}
		try {
			$jsonResponse = json_decode($body, false, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException $exception) {
			throw new Exceptions\BadResponseException(sprintf('Bad API response from "%s": content is not valid JSON, error: "%s"', $url, $exception->getMessage()));
		}
		if (isset($jsonResponse->error)) {
			if ($jsonResponse->error === self::FOLDING_ERROR_NOT_FOUND) {
				throw new Exceptions\NotFoundException();
			} else {
				throw new Exceptions\ApiErrorException($jsonResponse->error);
			}
		}
		return $jsonResponse;
	}

	abstract function getUrl(string $id, bool $api = false);

	abstract function load(array $curlOpts = []);
}