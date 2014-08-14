<?php
session_start();
include_once("library.php");

if (isset($_POST["stats"])) {
	echo getJSON();
}

if (isset($_POST["commID"])) {
	$profile = getProfileData($_POST["commID"]);
	if ($profile["steamID64"] != "") {
		$_SESSION['steamID64'] = $profile["steamID64"];
		$_SESSION["steamID"] = $profile["steamID"];
		$_SESSION["avatar"] = $profile["avatar"];
		$_SESSION["custom_url"] = $profile["custom_url"];

		//Will add user to DB if this is user's first visit
		isTracked();
		
		$steamID64 = $profile["steamID64"];
		$steamID = $profile["steamID"];
		$avatar = $profile["avatar"];
		$custom_url = $profile["custom_url"];
		$out = array("steamID64" => $steamID64, "steamID" => $steamID, "avatar" => $avatar, "custom_url" => $custom_url);
		echo json_encode($out);
	} else {
		echo "{}";
	}
}

if (isset($_POST["tracking"])) {
	updateTracking();
}

if (isset($_POST["graph"])) {
	getGraphData();
}

function getJSON() {
	$api_key = "4E60A5F9D4614BDBA11B6096F647BD85";
	$steam_id = $_SESSION["steamID64"];
	$app_id = "730";
	$url = 'http://api.steampowered.com/ISteamUserStats/GetUserStatsForGame/v0002/?appid=%s&key=%s&steamid=%s';
	$url = sprintf($url, $app_id, $api_key, $steam_id);
	$json_raw = file_get_contents($url);
	$json = json_decode($json_raw, true);

	$general = array();
	$stats = $json["playerstats"]["stats"];

	foreach ($stats as $stat) {
		$name = $stat["name"];
		$general[$name] = $stat["value"];
	}

	global $weaponList, $mapList;

	//Add accuracy data
	foreach ($weaponList as $weapon => $attr) {
		if (array_key_exists("total_hits_" . $weapon, $general) && array_key_exists("total_shots_" . $weapon, $general)) {
			$general["accuracy_" . $weapon] = calculateAccuracy($general, $weapon);
		}
	}
	//Reformat time_played
	$general["total_time_played"] = getTimePlayed($steam_id) . " hours";
	$general["total_money_earned"] = "$" . number_format(floatval($general["total_money_earned"]));

	//Favorite Weapon (by #kills)
	reset($weaponList);
	$first_key = key($weaponList);
	reset($mapList);
	$first_map = key($first_map);
	$fav_weapon = array("name" => $first_key, "value" => $general["total_kills_" . $first_key]);
	$fav_map = array("name" => $first_map, "value" => $general['total_rounds_played_' . $first_map]);
	foreach ($general as $name => $value) {
		$name = preg_replace('/total_kills_/', '', $name);
		if (array_key_exists($name, $weaponList) && $value > $fav_weapon["value"]) {
			$fav_weapon["name"] = $name;
			$fav_weapon["value"] = $value;
		}
		$name = preg_replace('/total_rounds_map_/', '', $name);
		if (array_key_exists($name, $mapList) && $value > $fav_map["value"]) {
			$fav_map["name"] = $name;
			$fav_map["value"] = $value;
		}
	}
	$general["fav_weapon_name"] = $weaponList[$fav_weapon["name"]][0];
	$general["fav_weapon_kills"] = $fav_weapon["value"];
	$general["fav_weapon_hits"] = $general["total_hits_" . $fav_weapon["name"]];
	$general["fav_weapon_shots"] = $general["total_shots_" . $fav_weapon["name"]];
	$general["fav_weapon_accuracy"] = calculateAccuracy($general, $fav_weapon["name"]);
	$general["fav_weapon_img"] = "resources/images/weapons/weapon_" . $fav_weapon["name"] . ".png";

	$general["fav_map_name"] = $mapList[$fav_map["name"]][0];
	$general["fav_map_wins"] = $general["total_wins_map_" . $fav_map["name"]];
	$general["fav_map_rounds"] = $fav_map["value"];
	$general["fav_map_img"] = "resources/images/maps/map_" . $fav_map["name"] . ".png";

	//Favorite Map (by #rounds)

	return json_encode($general);
}

function calculateAccuracy($array, $weapon) {
	$shots = $array["total_shots_" . $weapon];
	$hits = $array["total_hits_" . $weapon];
	$accuracy = round((floatval($hits)/floatval($shots))*100, 2) . "%";
	return $accuracy;
}

function getMaxItem($array, $key) {
	$max = $array[0][$key];
	foreach ($array as $gun => $gun_stats) {
		if ($gun_stats[$key] > $max[$key]) {
			$max = $array[$gun];
			$max["Name"] = $gun;
		}
	}
	return $max;
}

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

	$stats_new["total_time_played"] = getTimePlayed($steam_id);
	return $stats_new;
}

function getTimePlayed($steam_id) {
	global $API_KEY, $APP_ID;
	$url = "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=$API_KEY&steamid=$steam_id&format=json";
	$json_raw = file_get_contents($url);
	$json = json_decode($json_raw, true);

	foreach ($json["response"]["games"] as $game) {
		if ($game["appid"] == $APP_ID) {
			return round(intval($game["playtime_forever"])/60.0, 2);
		}		
	}
}

function getProfileData($communityID){
	$url = $communityID . "?xml=1";
	$profile = simplexml_load_file($url);
	$profile_data = array();
	$profile_data["steamID64"] = (string)$profile->steamID64;
	$profile_data["steamID"] = (string)$profile->steamID;
	$profile_data["avatar"] = (string)$profile->avatarMedium;
	$profile_data["custom_url"] = (string)$profile->customURL;
	return $profile_data;
}

function updateTracking() {
	$tracked = isTracked();
	global $SERVER, $USERNAME, $PASSWORD, $DATABASE;
	$db_connection = new mysqli($SERVER, $USERNAME['super'], $PASSWORD['super'], $DATABASE);
	if (mysqli_connect_errno()) {
		echo "error: " . mysqli_connect_error();
		return null;
	}

	$msg = "Track Stats";
	$error = "error: couldn't remove tracker";
	$newVal = FALSE;
	if(!$tracked) {
		$msg = "Stop Tracking Stats";
		$error = "error: couldn't add tracker";
		$newVal = TRUE;
	}

	$stmt = $db_connection->stmt_init();
	$stmt->prepare("UPDATE stats_user SET tracking = ? WHERE steamID64 = ?");
	$stmt->bind_param("ii", $newVal, $_SESSION["steamID64"]);
	if(!$stmt->execute()) {
		echo $error;
	} else {
		echo $msg;
	}
	$stmt->close();
	$db_connection->close();
}

function getGraphData() {
	global $SERVER, $USERNAME, $PASSWORD, $DATABASE;
	$db_connection = new mysqli($SERVER, $USERNAME['super'], $PASSWORD['super'], $DATABASE);
	if (mysqli_connect_errno()) {
		echo "error: " . mysqli_connect_error();
		return null;
	}
	$stats = array("kd" => array(), "wlr" => array(), "acc" => array(), "wlm" => array(), "hs" => array());
	$stmt = $db_connection->stmt_init();
	$stmt->prepare("SELECT time_played, kills, deaths, wins, total_rounds, shots_fired, shots_hit, matches_won, matches_played, headshots FROM stats_log WHERE steamID64 = ?");
	$stmt->bind_param("i", $_SESSION["steamID64"]);
	$stmt->execute();
	$stmt->bind_result($time_played, $kills, $deaths, $round_wins, $round_total, $shots, $hits, $match_wins, $match_total, $headshots);
	$results = false;
	while ($stmt->fetch()) {
		$results = true;
		array_push($stats["kd"], array($time_played, round(intval($kills)/intval($deaths), 4)));
		array_push($stats["wlr"], array($time_played, intval($round_wins)/intval($round_total)));
		array_push($stats["acc"], array($time_played, round(intval($hits)/intval($shots)*100, 4)));
		array_push($stats["hs"], array($time_played, round(intval($headshots)/intval($kills)*100, 4)));
	}
	$stmt->close();
	$db_connection->close();
	//echo json_encode($stats);
	

	if ($results) {
		echo json_encode($stats);
	} else {
		echo $_SESSION['steamID'] . " doesn't have any recorded stats.";
	}
	
}
?>