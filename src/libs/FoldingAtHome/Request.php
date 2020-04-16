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
	 * @throws \Exception
	 */
	public function fileGetContent(string $url, array $curlOpts = []) {
		$curl = curl_init($url);
		$curlOpts[CURLOPT_RETURNTRANSFER] = true;
		$curlOpts[CURLOPT_HEADER] = true;
		curl_setopt_array($curl, $curlOpts);
		$curlResponse = curl_exec($curl);
		if ($curlResponse === false) { // @TODO translate to CURLE_something error https://www.php.net/manual/en/function.curl-errno.php
			if (curl_errno($curl) === CURLE_OPERATION_TIMEOUTED) {
				throw new Exceptions\ApiTimeoutException('API request timeouted.');
			} else {
				$curlErrno = curl_errno($curl);
				throw new Exceptions\BadRequestException('API request CURL error ' . $curlErrno . ' (' . $this->getCurlConstantName($curlErrno) . '): "' . curl_error($curl) . '"');
			}
		}
		list($header, $body) = explode("\r\n\r\n", $curlResponse, 2);
		if (!$body) {
			$responseCode = trim(explode(PHP_EOL, $header)[0]);
			throw new Exceptions\BadResponseException('Bad API response from "' . $url . '": "' . $responseCode . '".');
		}
		$jsonResponse = $this->jsonDecode($body);
		if (isset($jsonResponse->error)) {
			if ($jsonResponse->error === self::FOLDING_ERROR_NOT_FOUND) {
				throw new Exceptions\NotFoundException();
			} else {
				throw new Exceptions\ApiErrorException($jsonResponse->error);
			}
		}
		return $jsonResponse;
	}

	/**
	 * Decodes a JSON string
	 *
	 * @param string $jsonText
	 * @param bool $assoc true to return associative array or stdClass otherwise
	 * @return array|\stdClass
	 * @throws Exceptions\BadResponseException
	 */
	private function jsonDecode(string $jsonText, bool $assoc = false) {
		try {
			return json_decode($jsonText, $assoc, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException $exception) {
			throw new Exceptions\BadResponseException(sprintf('Bad API response: content is not valid JSON, error: "%s"', $exception->getMessage()));
		}
	}

	/**
	 * Translate CURL error code to it's constant
	 *
	 * @param int $curlErrorCode
	 * @return string
	 * @throws \Exception
	 */
	private function getCurlConstantName(int $curlErrorCode): string {
		$curlConstants = get_defined_constants(true)['curl'];
		foreach ($curlConstants as $curlConstantText => $curlConstantNumber) {
			if ($curlConstantNumber === $curlErrorCode && substr($curlConstantText, 0, 6) === 'CURLE_') {
				return $curlConstantText;
			}
		}
		throw new \Exception('Invalid CURL error number.');
	}


	abstract function getUrl(string $id, bool $api = false);

	abstract function load(array $curlOpts = []);
}