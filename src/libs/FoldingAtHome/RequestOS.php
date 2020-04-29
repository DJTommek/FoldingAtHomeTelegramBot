<?php

namespace FoldingAtHome;

use FoldingAtHome\OSStats;

class RequestOS extends Request
{

	public function getUrl(): string {
		return 'https://api.foldingathome.org/os';
	}

	/**
	 * @param array $curlOpts
	 * @return OSStats[]
	 * @throws Exceptions\ApiErrorException
	 * @throws Exceptions\ApiTimeoutException
	 * @throws Exceptions\BadRequestException
	 * @throws Exceptions\BadResponseException
	 * @throws Exceptions\NotFoundException
	 */
	public function load(array $curlOpts = []) {
		$apiUrl = $this->getUrl();
		$arrayResponse = $this->fileGetContent($apiUrl, $curlOpts + [
				CURLOPT_CONNECTTIMEOUT => self::TIMEOUT,
				CURLOPT_TIMEOUT => self::TIMEOUT,
			]); // override values from outside
		$result = [];
		$total = [];
		foreach ($arrayResponse as $i => $osData) {
			if ($i === 0) {
				foreach ($osData as $k => $osValues) {
					if ($k === 0) {
						$total[$k] = 'Total';
					} else {
						$total[$k] = 0;
					}
				}
				continue; // Ignore table header

			}
			foreach ($osData as $k => $osValues) {
				if (is_int($osValues)) {
					$total[$k] += $osValues;
				}
			}
			$result[] = OSStats::createFromArray($osData);
		}
		$result[] = OSStats::createFromArray($total);
//		$total = new OSStats();
		return $result;
	}
}