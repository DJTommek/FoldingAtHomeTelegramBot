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
	public static function getAbbrFromTimezone(\DateTimeZone $dateTimeZone) {
		$dateTime = new \DateTime();
		$dateTime->setTimeZone($dateTimeZone);
		return $dateTime->format('T');
	}

	public static function getTimezoneContinents() {
		$timezoneGroups = [];
		foreach (\DateTimeZone::listIdentifiers() as $timezone) {
			$timezoneExploded = explode('/', $timezone);
			$group = array_shift($timezoneExploded);
			$timezoneGroups[$group] = $group;
		}
		return array_values($timezoneGroups);
	}

	/**
	 * @param string $timezoneArea string from getTimezoneContinents
	 * @param bool $withContinent
	 * @return array
	 * @throws \Exception
	 */
	public static function getTimezoneAreas(?string $timezoneArea = null, bool $withContinent = false) {
		if (is_null($timezoneArea)) {
			return \DateTimeZone::listIdentifiers();
		}
		if (!in_array($timezoneArea, self::getTimezoneContinents())) {
			throw new \Exception(sprintf('"%s" is not valid timezone continent', $timezoneArea));
		}
		$timezoneSubzones = [];
		foreach (\DateTimeZone::listIdentifiers() as $timezone) {
			$timezoneExploded = explode('/', $timezone);
			if (array_shift($timezoneExploded) === $timezoneArea) {
				$subzone = join('/', $timezoneExploded);
				if ($withContinent) {
					$timezoneSubzones[$timezone] = $timezone;
				} else {
					$timezoneSubzones[$subzone] = $subzone;
				}
			}
		}
		return array_values($timezoneSubzones);
	}
}