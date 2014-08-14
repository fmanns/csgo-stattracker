<?php
	session_start();
	include_once("library.php");

	$value = "";
	$alert = "";
	$class = "";
	if (isset($_GET["id"])) {
		$id = $_GET["id"];
		$url = "http://steamcommunity.com/id/$id";
		$profile = getProfileData($url);
		//Attempt assuming user gave customURL
		if (!getProfileData($url)) {
			//Try again - user might have given a steam64ID
			$url = "http://steamcommunity.com/profiles/$id";
			if (!getProfileData($url)) {
				session_unset();
				$alert = "Could not locate Steam account using '$id'";
				$class = "warning";
				if (is_numeric($id)) {
					$value = "http://steamcommunity.com/profiles/$id";
				} else {
					$value = "http://steamcommunity.com/id/$id";
				}
				
			}
		}
	}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>CS 4720 HW3 - Frank Manns</title>
	<link rel="icon" href="resources/images/favicon.gif" type="image/x-icon">
	<link type='text/css' href='//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css' rel='stylesheet'>
	<link rel="stylesheet" type="text/css" href="resources/css/styles.css">
	<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="resources/css/font-awesome-4.0.3/css/font-awesome.min.css">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script src="resources/scripts/script.js"></script>
	<script src="resources/scripts/graphs.js"></script>
</head>
<body>
	<header class="siteHeader">
		<?php
		global $value, $alert, $class;
		if (!loggedin()) {
			echo "
			<img id='banner' src='resources/images/header_csgo_landscape.png' alt='Site Header'>
			<div id='loginInfo'>
				<h3>Enter Community Profile Link</h3>
				<form action='stats.php' method='post' class='ajax'>
					<input class='$class' type='text' id='communityID' title='CommunityID' value='$value' name='commID'>
					<input type='submit' id='submitCommunityID' class='submitButton'>
					<div id='alert'>$alert</div>
				</form>
			</div>";
			
		} else {
			echo '<img id="banner" class="thin" src="resources/images/header_csgo_thin.png" alt="Site Header">';
		}
		?>
	</header>
	<?php
		if (!loggedin()) {
			echo '<div id="main" class="dontDisplay">';
		} else {
			echo '<div id="main">';
		}
	?>
		<nav class="siteNav">
			<ul>
				<li class="tab general">General</li>
				<li class="tab weapons">Weapons</li>
				<li class="tab maps">Maps</li>
				<li class="tab graphs">Graphs</li>
			</ul>
			<div id="profileInfo">
				<?php profileInfo(); ?>
			</div>
		</nav>
		<div class="mainContent">
			<div class="contentPane">
				<div class="sortHeader general">
					<div class="col1 third"></div>
					<div class="col2 third"><i id="refreshGeneral" class="fa fa-refresh fa-2x refresher"></i></div>
				</div>
				<div class="container">
					<div class="loader"></div>
					<article id="generalMain">
						<div id="statTable">
							<?php printGeneralStats(); ?>
						</div>
					</article>
					<aside id="generalSide">
						<h4>Favorite Weapon</h4>
						<article class="statBox">
							<header id="fav_weapon_name" class="stat">
								---
							</header>
							<section>
								<img id="fav_weapon_img" class="stat" src="#" alt="">
								<table>
									<tr>
										<td>Kills</td>
										<td id="fav_weapon_kills" class="right stat">---</td>
									</tr>
									<tr>
										<td>Shots Fired</td>
										<td id="fav_weapon_shots" class="right stat">---</td>
									</tr>
									<tr>
										<td>Hits</td>
										<td id="fav_weapon_hits" class="right stat">---</td>
									</tr>
									<tr>
										<td>Accuracy</td>
										<td id="fav_weapon_accuracy" class="right stat">---</td>
									</tr>
								</table>
							</section>
						</article>
						<div class="clearFloat"></div>
						<h4>Favorite Map</h4>
						<article class="statBox">
							<header id="fav_map_name" class="stat">
								---
							</header>
							<section>
								<img id="fav_map_img" class="stat" src="#" alt="">
								<table>
									<tr>
										<td>Wins</td>
										<td id="fav_map_wins" class="right stat">---</td>
									</tr>
									<tr>
										<td>Rounds Played</td>
										<td id="fav_map_rounds" class="right stat">---</td>
									</tr>
								</table>
							</section>
						</article>
					</aside>
				</div>
			</div>
			<div class="contentPane">
				<div class="sortHeader">
					<div class="col1 third">
						<div class="picker filter">
							<h2>All Weapons</h2><i class="fa fa-caret-down fa-2x"></i>
							<div class="dropdown">
								<ul>
									<li id="value_1:">All Weapons</li>
									<li id="value:.pistol">Pistols</li>
									<li id="value:.smg">SMGs</li>
									<li id="value:.rifle">Rifles</li>
									<li id="value:.heavy">Heavy</li>
									<li id="value:.equipment">Equipment</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="col2 third"><i id="refreshWeapons" class="fa fa-refresh fa-2x refresher"></i></div>
					<div class="picker sort">
						<h2>Name</h2><i class="fa fa-caret-down fa-2x"></i>
						<div class="dropdown">
							<ul>
								<li id="value_2:header">Name</li>
								<li id="value:.kills">Kills</li>
								<li id="value:.shots">Shots Fired</li>
								<li id="value:.hits">Hits</li>
								<li id="value:.accuracy">Accuracy</li>
							</ul>
						</div>
					</div>
				</div>
				<div id="weaponsContainer" class="container sortable">
					<div class="loader" id="weaponsLoader"></div>
					<?php
						printWeaponArticles();
					?>
				</div>
			</div>
			<div class="contentPane">
				<div class="sortHeader">
					<div class="col1 third">
						<div class="picker filter">
							<h2>All Maps</h2><i class="fa fa-caret-down fa-2x"></i>
							<div class="dropdown dontDisplay">
								<ul>
									<li id="value_3:">All Maps</li>
									<li id="value:.defusal">Bomb Defusal</li>
									<li id="value:.hostage">Hostage Rescue</li>
									<li id="value:.race">Arms Race</li>
									<li id="value:.demolition">Demolition</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="col2 third"><i id="refreshMaps" class="fa fa-refresh fa-2x refresher"></i></div>
					<div class="picker sort">
						<h2>Name</h2><i class="fa fa-caret-down fa-2x"></i>
						<div class="dropdown dontDisplay">
							<ul>
								<li id="value_4:header">Name</li>
								<li id="value:.wins">Wins</li>
								<li id="value:.rounds">Rounds</li>
							</ul>
						</div>
					</div>
				</div>
				<div id="mapsContainer" class="container sortable">
					<div class="loader" id="mapLoader"></div>
					<?php
						printMapArticles();
					?>
				</div>
			</div>
			<div class="contentPane">
				<div class="container">
					<div id="graphs_loading">Loading...</div>
					<div id="chart_kd" class="chart"></div>
					<div id="chart_acc" class="chart"></div>
					<div id="chart_hs" class="chart"></div>
				</div>
			</div>
		</div>
		<footer class="siteFooter">
			<div id="footer-contact">
				Frank Manns - frm5tu<br>
				CS 4720 - Web and Mobile Systems<br>
				University of Virginia
			</div>
			<div id="footer-api">
				<a href="http://steampowered.com">Powered by Steam</a>
			</div>
		</footer>
	</div>
</body>
</html>
<?php
function printWeaponArticles() {
	global $weaponList;

	foreach ($weaponList as $key => $valArr) {
		echo printWeaponBox($valArr[0], $key, $valArr[1]);
	}
}

function getProfileData($community_id_url) {
	$url = $community_id_url . "?xml=1";
	$profile = simplexml_load_file($url);
	$profile_data = array();
	$profile_data["steamID64"] = (string)$profile->steamID64;
	$profile_data["steamID"] = (string)$profile->steamID;
	$profile_data["avatar"] = (string)$profile->avatarMedium;

	if ($profile_data["steamID64"] !== "") {
		$_SESSION['steamID64'] = $profile_data["steamID64"];
		$_SESSION["steamID"] = $profile_data["steamID"];
		$_SESSION["avatar"] = $profile_data["avatar"];
		return true;
	} else {
		return false;
	}
}

function printMapArticles() {
	$article_structure =
		'<article id="%s" class="statBox mapBox %s">
			<header>
				%s
			</header>
			<section>
				<img src="resources/images/maps/map_%s.png" alt="%s">
				<table>
					<tr>
						<td>Wins</td>
						<td id="total_wins_map_%s" class="right stat wins">---</td>
					</tr>
					<tr>
						<td>Rounds Played</td>
						<td id="total_rounds_map_%s" class="right stat rounds">---</td>
					</tr>
				</table>
			</section>
		</article>';

	global $mapList;

	foreach ($mapList as $name => $valArr) {
		$internal_name = $name;
		$name = $valArr[0];
		$mode = $valArr[1];
		echo sprintf($article_structure, $internal_name, $mode, $name, $internal_name,
			$key, $internal_name, $internal_name);
	}
}

function profileInfo() {
	$tracked = "Track Stats";
	if (isTracked()) {
		$tracked = "Stop Tracking Stats";
	}
	$avatar = $_SESSION["avatar"];
	if ($avatar == "") {
		$avatar = "#";
	}
	$html = "
		<div class='profileWrapper'>
			<h2 id='profileName'>%s</h2>
			<img id='avatar' src='%s' alt='Avatar'>
			<div class='dropdown dontDisplay'>
				<div class='arrow'></div>
				<ul>
					<li><a id='profileLink' href='http://steamcommunity.com/profiles/%s'>View Profile</a></li>
					<li><a id='logout' href='logout.php'>Logout</a></li>
					<li id='tracking' class='padding-10'>%s</li>
				</ul>
			</div>
		</div>
	";
	printf($html, $_SESSION["steamID"], $avatar, $_SESSION["steamID64"], $tracked);
}

function loggedin() {
	return isset($_SESSION["steamID64"]);
}

function printWeaponBox($displayName, $hiddenName, $class) {
	$template =
	'<article id="%s" class="statBox weaponBox %s">
		<header>
			%s
		</header>
		<section>
			<img src="resources/images/weapons/weapon_%s.png" alt="%s">
			<table>
				<tr>
					<td>Kills</td>
					<td id="total_kills_%s" class="right stat kills">---</td>
				</tr>
				<tr>
					<td>Shots Fired</td>
					<td id="total_shots_%s" class="right stat shots">---</td>
				</tr>
				<tr>
					<td>Hits</td>
					<td id="total_hits_%s" class="right stat hits">---</td>
				</tr>
				<tr>
					<td>Accuracy</td>
					<td id = "accuracy_%s" class="right stat accuracy">---</td>
				</tr>
			</table>
		</section>
	</article>';
	return sprintf($template, $hiddenName, $class, $displayName, $hiddenName, $displayName, $hiddenName, $hiddenName, $hiddenName, $hiddenName);
}

function printGeneralStats() {
	$stats = array(
		"Kills" => "total_kills",
		"Deaths" => "total_deaths",
		"Time Played" => "total_time_played",
		"Bombs Planted" => "total_planted_bombs",
		"Bombs Defused" => "total_defused_bombs",
		"Wins" => "total_wins",
		"Rounds Played" => "total_rounds_played",
		"Matches Won" => "total_matches_won",
		"Matches Played" => "total_matches_played",
		"Damage Done" => "total_damage_done",
		"Money Earned" => "total_money_earned",
		"Pistol Rounds Won" => "total_wins_pistolround",
		"Headshot Kills" => "total_kills_headshot",
		"Kills with Enemy Weapon" => "total_kills_enemy_weapon",
		"Weapons Donated" => "total_weapons_donated",
		"Windows Broken" => "total_broken_windows",
		"Blinded Enemies Killed" => "total_kills_enemy_blinded",
		"Knife Fight Kills" => "total_kills_knife_fight",
		"Kills Against Zoomed Snipers" => "total_kills_against_zoomed_sniper",
		"Dominations" => "total_dominations",
		"Domination Overkills" => "total_domination_overkills",
		"Revenges" => "total_revenges",
		"Shots Hit" => "total_shots_hit",
		"Shots Fired" => "total_shots_fired",		
		"MVPs" => "total_mvps"
	);

	$template = 
	'<div class="row">
		<div class="col1 half">%s</div>
		<div id="%s" class="col2 half stat">---</div>
	</div>';
	foreach ($stats as $name => $variable) {
		echo sprintf($template, $name, $variable);
	}
}
?>