<?php
include_once("secretstuff.php");

$weaponList = array(
	"ak47" => array("AK-47", "rifle"),
	"aug" => array("AUG", "rifle"),
	"awp" => array("AWP", "rifle"),
	"bizon" => array("PP-Bizon", "smg"),
	"deagle" => array("Desert Eagle", "pistol"),
	"elite" => array("Dual Berettas", "pistol"),
	"famas" => array("FAMAS", "rifle"),
	"fiveseven" => array("Five-Seven", "pistol"),
	"g3sg1" => array("G3SG1", "rifle"),
	"galilar" => array("Galil AR", "rifle"),
	"glock" => array("Glock-18", "pistol"),
	"hkp2000" => array("P2000", "pistol"),
	"m4a1" => array("M4A1", "rifle"),
	"m249" => array("M249", "heavy"),
	"mac10" => array("MAC-10", "smg"),
	"mag7" => array("MAG-7", "heavy"),
	"mp7" => array("MP7", "smg"),
	"mp9" => array("MP9", "smg"),
	"negev" => array("Negev", "heavy"),
	"nova" => array("Nova", "heavy"),
	"p90" => array("P90", "smg"),
	"p250" => array("P250", "pistol"),
	"sawedoff" => array("Sawed-Off", "heavy"),
	"scar20" => array("SCAR-20", "rifle"),
	"sg556" => array("SG 553", "rifle"),
	"ssg08" => array("SSG 08", "rifle"),
	"taser" => array("Taser", "equipment"),
	"tec9" => array("Tec-9", "pistol"),
	"ump45" => array("UMP-45", "smg"),
	"xm1014" => array("XM1014", "heavy"),
	"hegrenade" => array("HE Grenade", "equipment"),
	"molotov" => array("Molotov Cocktail", "equipment"),
	"knife" => array("Knife", "equipment")
);

$mapList = array(
	"cs_assault" => array("Assault", "hostage"),
	"de_aztec" => array("Aztec", "defusal"),
	"ar_baggage" => array("Baggage", "race"),
	"de_bank" => array("Bank", "demolition"),
	"de_cbble" => array("Cbble", "defusal"),
	"de_dust" => array("Dust", "defusal"),
	"de_dust2" => array("Dust II", "defusal"),
	"de_inferno" => array("Inferno", "defusal"),
	"cs_italy" => array("Italy", "hostage"),
	"de_lake" => array("Lake", "demolition"),
	"cs_militia" => array("Militia", "hostage"),
	"ar_monastery" => array("Monastery", "race"),
	"de_nuke" => array("Nuke", "defusal"),
	"cs_office" => array("Office", "hostage"),
	"de_safehouse" => array("Safehouse", "demolition"),
	"ar_shoots" => array("Shoots", "race"),
	"de_shorttrain" => array("Shorttrain", "demolition"),
	"de_stmarc" => array("St. Marc", "demolition"),
	"de_sugarcane" => array("Sugarcane", "demolition"),
	"de_train" => array("Train", "defusal"),
	"de_vertigo" => array("Vertigo", "defusal")
);

function isTracked() {
	global $SERVER, $USERNAME, $PASSWORD, $DATABASE;
	$db_connection = new mysqli($SERVER, $USERNAME['super'], $PASSWORD['super'], $DATABASE);
	if (mysqli_connect_errno()) {
		echo("Can't connect to database");
		return null;
	}
	$tracked = false;
	$hasAccount = true;
	$stmt = $db_connection->stmt_init();
	$stmt->prepare("SELECT steamID64, tracking FROM stats_user WHERE steamID64 = ?");
	$stmt->bind_param("i", $_SESSION["steamID64"]);
	$stmt->execute();
	$stmt->bind_result($id, $tracked);
	if (!$stmt->fetch()) {
		$hasAccount = false;
	}
	$stmt->close();
	if (!$hasAccount) {
		$stmt = $db_connection->stmt_init();
		$stmt->prepare("INSERT INTO stats_user(steamID64, steamID) VALUES(?, ?)");
		$stmt->bind_param("is", $_SESSION["steamID64"], $_SESSION["steamID"]);
		$stmt->execute();
		$stmt->close();
		$tracked = false;
	}
	$db_connection->close();
	return $tracked;
}
?>