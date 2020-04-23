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

	/**
	 * Get list of groups for timezones (America, Europe, Asia, etc)
	 *
	 * @return string[]
	 */
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
	 * Get list of all timezones
	 *
	 * @return \Datetimezone[]
	 */
	public static function getTimezones() {
		return self::timezonesToClass(\DateTimeZone::listIdentifiers());
	}

	/**
	 * @param string $timezoneContinent string from getTimezoneContinents
	 * @return array|\Datetimezone[]
	 * @throws \Exception
	 */
	public static function getTimezonesByContinent(string $timezoneContinent) {
		if (!in_array($timezoneContinent, self::getTimezoneContinents())) {
			throw new \Exception(sprintf('"%s" is not valid timezone continent', $timezoneContinent));
		}
		$timezoneSubzones = [];
		foreach (self::getTimezones() as $timezone) {
			$timezoneExploded = explode('/', $timezone->getName());
			if (array_shift($timezoneExploded) === $timezoneContinent) {
				$timezoneSubzones[$timezone->getName()] = $timezone; // @TODO it seems that this grouping by name is not necessary, should be unique
			}
		}
		return array_values($timezoneSubzones);
	}

	/**
	 * Convert array of timezone strings into array of timezone objects
	 *
	 * @param string[] $timezones
	 * @return \Datetimezone[]
	 */
	public static function timezonesToClass(array $timezones) {
		$result = [];
		foreach ($timezones as $timezone) {
			$result[] = new \DateTimeZone($timezone);
		}
		return $result;
	}

	/**
	 * Formatting offset in format "UTC+xx:yy", "UTC-xx:yy". If timezone is UTC, no offset is displayed, only text "UTC"
	 *
	 * @param \Datetime $datetime
	 * @return string UTC+09:30, UTC-05:00 or UTC
	 */
	public static function formatTimezoneOffset(\Datetime $datetime) {
		$offsetText = 'UTC';
		if ($datetime->getOffset() !== 0) {
			$offsetText .= $datetime->format('P');
		}
		return $offsetText;
	}
}