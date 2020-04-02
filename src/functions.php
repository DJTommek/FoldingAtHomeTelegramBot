<?php

/**
 * Simple "debug" tool
 */
function dd($mixed, $die = true) {
	printf('<pre>%s</pre>', var_export($mixed, true));
	if ($die) {
		die();
	}
}

function sToHuman($seconds) {
	$dtF = new \DateTime('@0');
	$dtT = new \DateTime("@$seconds");
	return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

function fileGetContent(string $url, array $curlOpts = []) {
	$curl = curl_init($url);
	$curlOpts[CURLOPT_RETURNTRANSFER] = true;
	$curlOpts[CURLOPT_HEADER] = true;
//	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//	curl_setopt($curl, CURLOPT_HEADER, true);
	curl_setopt_array($curl, $curlOpts);
	$curlResponse = curl_exec($curl);
	if ($curlResponse === false) { // @TODO translate to CURLE_something error https://www.php.net/manual/en/function.curl-errno.php
		throw new Exception('CURL error ' . curl_errno($curl));
	}
	list($header, $body) = explode("\r\n\r\n", $curlResponse, 2);
	if (!$body) {
		$responseCode = trim(explode(PHP_EOL, $header)[0]);
		$exceptionMessage = 'Bad API response from "' . $url . '": "' . $responseCode . '"';
		throw new Exception($exceptionMessage);
	}
	return $body;
}