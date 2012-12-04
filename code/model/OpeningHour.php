<?php


class OpeningHour extends DataObject {

	public static $db = array(
		"FromDay" => "Int",
		"UntilDay" => "Int",
		"FromHour" => "Int",
		"UntilHour" => "Int",
		"FromMinutes" => "Int",
		"UntilMinutes" => "Int",
		"Note" => "Varchar",
		"TimeZone" => "Varchar"
	);

	protected static $days = array(
		1 => "Monday",
		2 => "Tuesday",
		3 => "Wednesday",
		4 => "Thursday",
		5 => "Friday",
		6 => "Saturday",
		7 => "Sunday"
	);
		static function get_days() {return self::$days;}

	function NextOpeningTime() {

	}

	function IsOpenNow(){

	}

	function OpeningHoursForNextWeek(){

	}

	
}
