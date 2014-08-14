<?php

include_once("stats.php");

echo strtotime("10 hours 3 minutes 10 seconds");

/*
function getAllStats($steam_id) {
	$api_key = "4E60A5F9D4614BDBA11B6096F647BD85";
	$app_id = "730";
	$url = "http://api.steampowered.com/ISteamUserStats/GetUserStatsForGame/v0002/?appid=$app_id&key=$api_key&steamid=$steam_id";
	$json_raw = file_get_contents($url);
	$json = json_decode($json_raw, true);

	$stats = $json["playerstats"]["stats"];
	$stats_new = array();
	foreach ($stats as $stat) {
		$name = $stat["name"];
		$stats_new[$name] = $stat["value"];
	}
	
	//Get time played using 'GetOwnedGames' API call
	$url = "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=$api_key&steamid=$steam_id&format=json";
	$json_raw = file_get_contents($url);
	$json = json_decode($json_raw, true);

	foreach ($json["response"]["games"] as $game) {
		if ($game["appid"] == $app_id) {
			$stats_new["total_time_played"] = round(intval($game["playtime_forever"])/60.0, 2);
			break;
		}		
	}
	
	return $stats_new;
}
*/
?>