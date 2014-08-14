<?php
include_once("stats.php");
include_once("library.php");

$users = getTrackedUsers();
saveToDB($users);

function getTrackedUsers() {
	global $SERVER, $USERNAME, $PASSWORD, $DATABASE;
	$db_connection = new mysqli($SERVER, $USERNAME['super'], $PASSWORD['super'], $DATABASE);
	if (mysqli_connect_errno()) {
		echo("Can't connect to MySQL Server. Error code: " .  mysqli_connect_error());
		return null;
	}
	$users = array();
	$stmt = $db_connection->stmt_init();

	if($stmt->prepare("SELECT stats_user.steamID64, max(time_played) FROM stats_user LEFT OUTER JOIN stats_log ON stats_user.steamID64 = stats_log.steamID64 WHERE tracking = 1 GROUP BY stats_user.steamID64")) {
		$stmt->execute();
		$stmt->bind_result($id, $time);
		while ($stmt->fetch()) {
			$users[$id] = $time;
		}
	} else {
		echo "stmt error";
		return null;
	}
	$stmt->close();
	$db_connection->close();

	return $users;
}

function saveToDB($users) {
	$time = date(DATE_RSS);
	$header = "================================\n$time\n";
	$log = "";
	global $SERVER, $USERNAME, $PASSWORD, $DATABASE;
	$db_connection = new mysqli($SERVER, $USERNAME['super'], $PASSWORD['super'], $DATABASE);
	if (mysqli_connect_errno()) {
		$log .= mysqli_connect_error();
		return null;
	}
	foreach ($users as $id => $time) {
		$stats = getAllStats($id);
		$time_played = $stats["total_time_played"];
		global $INTERVAL;
		if ($time_played - $time > $INTERVAL) {
			$stmt = $db_connection->stmt_init();
			$kills = $stats["total_kills"];
			$deaths = $stats["total_deaths"];
			$wins = $stats["total_wins"];
			$rounds = $stats["total_rounds_played"];
			$shots = $stats["total_shots_fired"];
			$hits = $stats["total_shots_hit"];
			$matches_won = $stats["total_matches_won"];
			$matches_played = $stats["total_matches_played"];
			$headshots = $stats["total_kills_headshot"];
			

			$stmt->prepare("INSERT INTO stats_log(steamID64, time_played, kills, deaths, wins, total_rounds, shots_fired, shots_hit, matches_won, matches_played, headshots) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("iiiiiiiiiii", $id, $time_played, $kills, $deaths, $wins, $rounds, $shots, $hits, $matches_won, $matches_played, $headshots);
			if ($stmt->execute()) {
				$log .= "$id: stats updated\n";
			} else {
				$log .= "$id: $db_connection->error\n";
			}
			$stmt->close();
		}
	}
	$db_connection->close();
	if ($log != "") {
		file_put_contents("log.txt", $header . $log, FILE_APPEND);
	}
	echo "done";
}
?>