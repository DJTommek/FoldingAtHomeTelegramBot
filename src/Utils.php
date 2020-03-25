<?php

class Utils
{
	static function sToHuman($seconds) {
		$dtF = new \DateTime('@0');
		$dtT = new \DateTime("@$seconds");
		return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
	}

	static function numberFormat(int $number, int $decimals = 0): string {
		return \number_format($number, $decimals, '.', ' ');
	}
}