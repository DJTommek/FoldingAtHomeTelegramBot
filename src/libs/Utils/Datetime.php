<?php

namespace Utils;

class Datetime
{
	/**
	 * Get timezone abbreviation for a given time zone
	 *
	 * @author dustinalan https://www.php.net/manual/en/function.timezone-abbreviations-list.php#97472
	 * @param \DateTimeZone $dateTimeZone
	 * @return string
	 * @throws \Exception
	 */
	public static function getAbbrFromTZ(\DateTimeZone $dateTimeZone) {
		$dateTime = new \DateTime();
		$dateTime->setTimeZone($dateTimeZone);
		return $dateTime->format('T');
	}

	public static function getTZGroups() {
		$timezoneGroups = [];
		foreach (\DateTimeZone::listIdentifiers() as $timezone) {
			$timezoneExploded = explode('/', $timezone);
			$group = array_shift($timezoneExploded);
			$timezoneGroups[$group] = $group;
		}
		return array_values($timezoneGroups);
	}

	/**
	 * @param string $TZGroup
	 * @return array
	 * @throws \Exception
	 */
	public static function getTZSubzone(string $TZGroup) {
		if (!in_array($TZGroup, self::getTZGroups())) {
			throw new \Exception(sprintf('"%s" is not valid timezone group', $TZGroup));
		}
		$timezoneSubzones = [];
		foreach (\DateTimeZone::listIdentifiers() as $timezone) {
			$timezoneExploded = explode('/', $timezone);
			if (array_shift($timezoneExploded) === $TZGroup) {
				$subzone = join('/', $timezoneExploded);
				$timezoneSubzones[$subzone] = $subzone;
			}
		}
		return array_values($timezoneSubzones);
	}
}