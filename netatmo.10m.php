#!/opt/homebrew/bin/php
<?php
# <bitbar.title>WeatherMenu - Netatmo</bitbar.title>
# <bitbar.version>v1.0</bitbar.version>
# <bitbar.author>Dan Moren</bitbar.author>
# <bitbar.author.github>dbmoren</bitbar.author.github>
# <bitbar.desc>Gets weather from local Netatmo station. Requires configuration for device ID, Netatmo account information, and developer access.</bitbar.desc>
# <bitbar.dependencies>php</bitbar.dependencies>

// Your current timezone, as per this list: https://www.php.net/manual/en/timezones.php
$default_timezone = "America/New_York";

// Your Netatmo module's MAC address.
$device_id = "[MAC ADDRESS]";

// Client ID and Client Secret obtained from Netatmo Developer setup. (https://dev.netatmo.com/apps/)
$oauth_client_id = "[CLIENT ID]"; 
$oauth_client_secret = "[CLIENT SECRET]"; 

// Netatmo username and password.
$oauth_username = "USER@EXAMPLE.COM";
$oauth_password = "PASSWORD";
	
// Setup the cURL session to retrieve oAuth token.
$ch = curl_init();

// Set the options for the oAuth cURL session, as per Netatmo's guidelines:
// https://dev.netatmo.com/apidocumentation/oauth
$postfields = ["grant_type" => "password", 
		"Content-Type:" => "application/x-www-form-urlencoded;charset=UTF-8",
		"client_id" => $oauth_client_id,
		"client_secret" => $oauth_client_secret,
		"username" => $oauth_username,
		"password" => $oauth_password];

curl_setopt_array($ch, array( 
	CURLOPT_URL => "https://api.netatmo.com/oauth2/token",
	CURLOPT_POST => 1,
	CURLOPT_POSTFIELDS => $postfields,
	CURLOPT_RETURNTRANSFER => true
	)
);

// Execute cURL session; throw any errors. 
if( ! $oauth_data = curl_exec($ch)) {
	trigger_error(curl_error($ch)); 
} 
curl_close($ch);

// Format results from cURL session into an array.
$oauth_results = json_decode($oauth_data, true);

// Start actual retrieval request, using Netatmo API endpoint and oAuth token from above.
// https://dev.netatmo.com/apidocumentation/weather
$URL = "https://api.netatmo.com/api/getstationsdata?device_id=".urlencode($device_id)."&get_favorites=false";

$opts = ["http" => ["method" => "GET", "header" => "Authorization: Bearer ".$oauth_results["access_token"]]];
$context = stream_context_create($opts);

$weather_data = @file_get_contents($URL, false, $context);

$json = json_decode($weather_data, true);

// Retrieve data from JSON results. 
if(is_array($json)) {
	$temp_celsius = $json['body']['devices'][0]['modules'][0]['dashboard_data']['Temperature'];
	$humidity_percent = $json['body']['devices'][0]['modules'][0]['dashboard_data']['Humidity'];
	$trend = $json['body']['devices'][0]['modules'][0]['dashboard_data']['temp_trend'];
	$battery_percent = $json['body']['devices'][0]['modules'][0]['battery_percent'];
	$last_update= $json['body']['devices'][0]['modules'][0]['dashboard_data']['time_utc'];

	$temp_fahrenheit = round(($temp_celsius * 1.8) + 32);
	$date = new DateTime();
	$date->setTimestamp($last_update);
	$date->setTimezone(new DateTimeZone($default_timezone));
	$output_date = $date->format('Y-m-d H:i:s');

	switch(true) {
		case ($battery_percent >= 50):
			$battery_status_icon = "\u{1F7E2}";
			break;
		case ($battery_percent < 50 && $battery_percent > 10):
			$battery_status_icon = "\u{1F7E0}";
			break;
		case ($battery_percent <= 10):
			$battery_status_icon = "\u{1F534}";
			break;
	}

	switch($trend) {
		case "up";
			$trend_arrow = "\u{2191}";
			break;
		case "down";
			$trend_arrow = "\u{2193}";
			break;
		case "stable";
			$trend_arrow = null;
			break;
	}
	$output = array(
		$temp_fahrenheit."°".$trend_arrow, 
		"Humidity: ".$humidity_percent."%", 
		"Battery: ".$battery_percent."%".$battery_status_icon, 
		"Last Updated: ".$output_date
		);
} else {
	$output = array(
		"\u{26A0}",
		"Invalid data received",
		);
}
// Code for a future "feels like" update.
//$feels_like = -42.379 + (2.04901523 * $temp_fahrenheit) + (10.14333127 * $humidity_percent) - (0.22475541 * $temp_fahrenheit * $humidity_percent) - (6.83783 * 10 − 3 * $temp_fahrenheit ** 2) - (5.481717 * 10 − 2 * $humidity_percent ** 2) + (1.22874 * 10 − 3 * $temp_fahrenheit ** 2 * $humidity_percent) + (8.5282 * 10 − 4 * $temp_fahrenheit * $humidity_percent ** 2) - (1.99 * 10 − 6 * $temp_fahrenheit ** 2 * $humidity_percent ** 2);

// Format output for SwiftBar

foreach($output as $key => $value) {
	if ($key == 0) {
		echo $value."\n---";
	} else {
		echo "\n".$value." | href='https://my.netatmo.com/app/station'";
	}
}

?>
