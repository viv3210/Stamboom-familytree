<?php 
	if(session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {// session isn't started
		session_start();
	}
	$url = (isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:"http://illegalhost.com");
	$host = parse_url($url)["host"];
	/*if (($host !== "localhost") && (substr($host, -16) !== "vanbiervliet.org") && ($host !== "192.168.0.190")) {
		header('HTTP/1.0 404 Not Found');
		include $_SERVER["DOCUMENT_ROOT"].'/files/404.php';
		die();	}*/
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php 
	// header('Content-type: text/html; charset=UTF-8');
?>
<html>
<head><title>Stamboom van de familie VAN BIERVLIET</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="modal.css">
<script type="text/javascript" src="https://code.jquery.com/jquery-1.7.1.min.js"></script>

<script>
var Kids = [];var KidsID = [];var KidsBD = []; // Birth Date
var KidsDD = []; // Death Date
var KidsK = []; // Has children
var KidsColour = [];	// show if someone would have been private 19/03/2022
var KidsPStatus = [];	// show if someone would have been private 19/03/2022
//var I = [];
var Parents = ["","","","","",""];var ParentsID = [];var Partner = [];var PartnerID = [];var ParentColour=[0,0,0,0,0,0];
var PartnerBD = []; // Birth Date
var PartnerDD = []; // Death Date
var PartnerColour = [];	// In case of private
var PartnerMarriageDate = [];
var PartnerMarriagePlace = [];
var PBD = ["?","?","?","?","?","?"];var PDD = [];
var InfoText = "<em>Geen informatie</em>";
var tree = "";
//† °


<?php
// boolean privacy, standaard true, betekent dat bepaalde gegevens niet getoond worden
$privacy = TRUE;
if (!empty($_SESSION["userId"])) {$privacy = FALSE;}

$defaultTree = $myTree = "vanbiervliet";
if (isset($_GET["tree"])) { // Parameter meegegeven
	$myTree = $_GET["tree"];
}

// Connect to db
include $_SERVER['DOCUMENT_ROOT']."/files/db.php"; //replacement
/* NEEDS TO CHANGE ONCE OTHER TREES ARE IMPORTED */
//$dbname = "vanbie1q_vanbiervliet";

$root = $myTree;
$dbname = $myTree;
if (strpos($myTree, 'vanbiervliet') !== FALSE) {$root = "vanbiervliet"; $dbname = $dbnamevb;}
if (strpos($myTree, 'dejonckheere') !== FALSE) {$root = "dejonckheere"; $dbname = $dbnamedj;}
if (strpos($myTree, 'charels') !== FALSE) {$root = "charels"; $dbname = $dbnamech;}

/* END NEEDS TO CHANGE ONCE OTHER TREES ARE IMPORTED */

echo "//trying to connect to database: $dbnamevb\n";
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {echo "Cannot connect to database. Bye!";die;}

if (!mysqli_set_charset($conn, "utf8")) {			// Zorg ervoor dat op neostrada goede character set gebruikt wordt om é, ë, è, etc. weer te geven
    printf("Error loading character set utf8: %s\n", mysqli_error($conn));
    exit();
}

/*
if (!$db = getCurrentDb($conn, $myTree)) {
	// Oorspronkelijk: indien foute boom, dan stoppen. 
	// echo "alert(\"Geen stamboom voor familie '".$myTree."' gevonden\");\n</script>";die;
	// Veranderen in standaardboom
	$myTree = $defaultTree;
	if (!$db = getCurrentDb($conn, $myTree)) {
		// Nog altijd probleem? Da's raar.
		echo "alert(\"Geen stamboom voor familie '".$myTree."' gevonden\");\n</script>";die;
	}
}
*/
global $conn, $db, $myTree, $root;
echo "var tree = \"$myTree\";\n";
$myIDs["vanbiervliet"] = 718;
$myIDs["vanbiervliet2"] = 718;
$myIDs["dejonckheere"] = 9;
// $myIDs["dejonckheere"] = $myIDs["dejonckheere20170325213307"] = 9;
$myIDs["charels"] = $myIDs["charels2"] = 1;
//$myID=$myIDs[$myTree];
$myID=$myIDs[$root];
if (isset($_GET["ID"])) { // Parameter meegegeven
	$cuID = $_GET["ID"];
	if (!is_int($cuID)) {
		if ((strtolower(substr($cuID,0,1)) == "i") AND (is_numeric(substr($cuID,1)))) {
			echo "// En hier zijn we \n\n";
			$cuID = substr($cuID,1);
		}
	}
	$checkID = fn_dosql_all("i", "I_ID", $cuID);
	$myID = isset($checkID) ? $cuID : $myID;
}

$a_father = fn_parent($myID, "F");
$a_mother = fn_parent($myID, "M");
$ID_f = $a_father["I_ID"];if ($ID_f=="") {$ID_f="\"I0\"";}
$ID_m = $a_mother["I_ID"];if ($ID_m=="") {$ID_m="\"I0\"";}
$a_father_of_father = fn_parent($ID_f, "F");
$a_father_of_mother = fn_parent($ID_m, "F");
$a_mother_of_mother = fn_parent($ID_m, "M");
$a_mother_of_father = fn_parent($ID_f, "M");
$ID_ff = $a_father_of_father["I_ID"];if ($ID_ff=="") {$ID_ff="\"I0\"";}
$ID_fm = $a_mother_of_father["I_ID"];if ($ID_fm=="") {$ID_fm="\"I0\"";}
$ID_mf = $a_father_of_mother["I_ID"];if ($ID_mf=="") {$ID_mf="\"I0\"";}
$ID_mm = $a_mother_of_mother["I_ID"];if ($ID_mm=="") {$ID_mm="\"I0\"";}


$I["I0"]["FN"]="";$I["I0"]["NA"]="";$I["I0"]["FC"]="";$I["I0"]["BI"]["DA"]="";$I["I0"]["DE"]["DA"]="";
$InfoText = "";
echo "individuNummer = $myID\n";
echo "Parents[0] = \"". $a_father["I_FIRST_NAME"] ." ".$a_father["I_LAST_NAME"]."\";";
if (isPublic($a_father) == "mark") {echo "ParentColour[0]=1;";}
echo "ParentsID[0] = ".$ID_f.";\n";
echo "Parents[1] = \"". $a_father_of_father["I_FIRST_NAME"]." ".$a_father_of_father["I_LAST_NAME"]."\";";
if (isPublic($a_father_of_father) == "mark") {echo "ParentColour[1]=1;";}
echo "ParentsID[1] = ".$ID_ff.";\n";
echo "Parents[2] = \"".$a_mother_of_father["I_FIRST_NAME"]." ".$a_mother_of_father["I_LAST_NAME"]."\";";
if (isPublic($a_mother_of_father) == "mark") {echo "ParentColour[2]=1;";}
echo "ParentsID[2] = ".$ID_fm.";\n";
echo "Parents[3] = \"".$a_mother["I_FIRST_NAME"]." ".$a_mother["I_LAST_NAME"]."\";";
if (isPublic($a_mother) == "mark") {echo "ParentColour[3]=1;";}
echo "ParentsID[3] = ".$ID_m.";\n";
echo "Parents[4] = \"".$a_father_of_mother["I_FIRST_NAME"]." ".$a_father_of_mother["I_LAST_NAME"]."\";";
if (isPublic($a_father_of_mother) == "mark") {echo "ParentColour[4]=1;";}
echo "ParentsID[4] = ".$ID_mf.";\n";
echo "Parents[5] = \"".$a_mother_of_mother["I_FIRST_NAME"]." ".$a_mother_of_mother["I_LAST_NAME"]."\";";
if (isPublic($a_mother_of_mother) == "mark") {echo "ParentColour[5]=1;";}
echo "ParentsID[5] = ".$ID_mm.";\n\n";

// Geboortedata
$PB[0]=$a_father["I_BIRTH_DATE"];
$PB[1]=$a_father_of_father["I_BIRTH_DATE"];
$PB[2]=$a_mother_of_father["I_BIRTH_DATE"];
$PB[3]=$a_mother["I_BIRTH_DATE"];
$PB[4]=$a_father_of_mother["I_BIRTH_DATE"];
$PB[5]=$a_mother_of_mother["I_BIRTH_DATE"];
$PD[0]=$a_father["I_DEATH_DATE"];
$PD[1]=$a_father_of_father["I_DEATH_DATE"];
$PD[2]=$a_mother_of_father["I_DEATH_DATE"];
$PD[3]=$a_mother["I_DEATH_DATE"];
$PD[4]=$a_father_of_mother["I_DEATH_DATE"];
$PD[5]=$a_mother_of_mother["I_DEATH_DATE"];
for ($bid = 0;$bid<=5;$bid++) {
	$PBI[$bid] = fn_date($PB[$bid]);
	$PDI[$bid] = fn_date($PD[$bid]);
	$PDates[$bid] = fn_DateLine($PBI[$bid], $PDI[$bid]);
}
echo "PBD[0]=\"".$PDates[0]."\";";
echo "PBD[1]=\"".$PDates[1]."\";";
echo "PBD[2]=\"".$PDates[2]."\";";
echo "PBD[3]=\"".$PDates[3]."\";";
echo "PBD[4]=\"".$PDates[4]."\";";
echo "PBD[5]=\"".$PDates[5]."\";\n";

// Persoon

$person = fn_dosql_all("i", "I_ID", $myID);

echo "var I = \"{$person["I_FIRST_NAME"]} {$person["I_LAST_NAME"]}\";\n";
echo "var I2 = \"{$person["I_FIRST_NAME"]} " . mb_strtoupper($person["I_LAST_NAME"]) . "\";\n";	
$tgd = fn_DateLine(fn_date($person["I_BIRTH_DATE"]),fn_date($person["I_DEATH_DATE"]));
echo "var GSData = \"".$tgd."\";\n";

$a_kids = 0; //Totaal aantal kinderen
$a_rel = $person["I_FAMS_NUMBER"]; //Aantal Relaties
echo "PartnerMarriages = ".$a_rel.";\n";
$infoLines = 0;
//debug($infoLines);
if ($a_rel != 0) {
		for ($rc = 1; $rc <= $a_rel; $rc++) {
			$famsnr = "I_FAMS{$rc}_ID";
			$fams = $person[$famsnr];
			$family = fn_dosql_all("f", "F_ID", $fams);
			$pID = ($myID == $family["F_HUSBAND"]?$family["F_WIFE"]:$family["F_HUSBAND"]);
			$pID = ($pID?$pID:0);
			$partner = fn_dosql_all("i", "I_ID", $pID);
			// Added for partner privacy 2023-02-12
			if (isPublic($partner) == "mark") {echo "PartnerColour = 1;";}
			// End added for partner privacy 2023-02-12
			echo "Partner[".($rc-1)."]=\"".$partner["I_FIRST_NAME"]." ".$partner["I_LAST_NAME"]."\";\n";
			echo "PartnerID[".($rc-1)."]=\"".$pID."\";\n";
			echo "PartnerBD[".($rc-1)."]=\"".fn_DateLine(fn_date($partner["I_BIRTH_DATE"]),fn_date($partner["I_DEATH_DATE"]))."\";\n";
			echo "PartnerMarriageDate[".($rc-1)."]=\"".fn_date($family["F_MARRIAGE_DATE"])."\";\n";
			echo "PartnerMarriagePlace[".($rc-1)."]=\"".fn_date($family["F_MARRIAGE_PLACE"])."\";\n";
			$infoLines += (($family["F_MARRIAGE_DATE"] || $family["F_MARRIAGE_PLACE"])?1:0);
			//debug($infoLines);
			fn_kids($family);
		}
}

$b_alias = ($person["I_ALIAS"]!="")?true:false;
$b_bida = ($person["I_BIRTH_DATE"]!="")?true:false;
$b_bipl = ($person["I_BIRTH_PLACE"]!="")?true:false;
$b_deda = ($person["I_DEATH_DATE"]!="" && $person["I_DEATH_DATE"] !== "UNKNOWN")?true:false;
$b_depl = ($person["I_DEATH_PLACE"]!="")?true:false;
$b_occu = ($person["I_OCCUPATION"]!="")?true:false;

$infoLines += ($b_bida || $b_bipl)?1:0;
//$infoLines += (($b_deda && $person["I_DEATH_DATE"] !== "UNKNOWN") || $b_depl)?1:0;
$infoLines += ($b_deda || $b_depl)?1:0;
$infoLines += ($b_occu)?1:0;
echo "var infoLines = ".$infoLines.";\n";
echo "var info = {};\n";
echo "info.name = \"".$person["I_FIRST_NAME"] . " " . mb_strtoupper($person["I_LAST_NAME"]). "\";\n";
echo "info.dob = ".(($b_bida)?"\"".fn_date($person["I_BIRTH_DATE"])."\"":"\"\"").";\n";
echo "info.dod = ".(($b_deda)?"\"".fn_date($person["I_DEATH_DATE"])."\"":"\"\"").";\n";
echo "info.pob = ".(($b_bipl)?"\"".fn_date($person["I_BIRTH_PLACE"])."\"":"\"\"").";\n";
echo "info.pod = ".(($b_depl)?"\"".fn_date($person["I_DEATH_PLACE"])."\"":"\"\"").";\n";
echo "info.age = ".(fn_leeftijd($person)!==""?"\"".fn_leeftijd($person)."\"":"\"\"").";\n";
echo "info.occupation = ".($b_occu?"\"".$person["I_OCCUPATION"]."\"":"\"\"").";\n";
//echo "info.godfather = \"".$person["I_BAPTISM_GODFATHER"]."\";\n";

if ($b_alias) {
	echo "info.alias = \"".$person["I_ALIAS"]."\";\n";
	echo "I2 += \"<br>(".$person["I_ALIAS"].")\";\n";
}
// Get marriage date(s) and place(s)

if ($b_bida or $b_bipl) {	// Aangepast om leeftijd levende personen weer te geven
	$InfoText .= "<strong>Geboren:</strong> ";
	if ($b_bida and $b_bipl) {
		$InfoText .= fn_date($person["I_BIRTH_DATE"]).", ".$person["I_BIRTH_PLACE"];
		if (!($b_deda or $b_depl)) {
			if (fn_leeftijd($person)) {$InfoText .= " (".fn_leeftijd($person).")";}
		}	
	}
	else {
		$InfoText .= fn_date($person["I_BIRTH_DATE"]).$person["I_BIRTH_PLACE"];
		if (!($b_deda or $b_depl)) {
			if (fn_leeftijd($person)) {$InfoText .= " (".fn_leeftijd($person).")";}
		}	
	}
	$InfoText .= "<p>";

}
if ($b_deda or $b_depl) {
	$InfoText .= "<strong>Overleden:</strong> ";
	if ($b_deda and $b_depl) {
		$InfoText .= fn_date($person["I_DEATH_DATE"]).", ".$person["I_DEATH_PLACE"];
	}
	else {
		$InfoText .= fn_date($person["I_DEATH_DATE"]).$person["I_DEATH_PLACE"];
	}
	if (fn_leeftijd($person)) {$InfoText .= " (".fn_leeftijd($person).")";}
	$InfoText .= "<p>";
}
if ($b_occu) {
	$InfoText .= "<strong>Beroep:</strong> {$person["I_OCCUPATION"]}<p>";
}
if ($InfoText != "") {echo "InfoText = \"$InfoText\"";}	

$close = mysqli_close($conn);
if (!$close){echo "\nError closing SQL connection.<br>\n";}

function isPublic($p) {	// If privacy is on, returns TRUE if the person is death, FALSE if alive <== changed 19/03/2022: return "ok" if privacy not important, "hide" if private and user not logged in, "mark" if private but user logged in
	// Rule: 	living person: private
	// 			dead person only displayed when born > 100 years ago. For example Bernard Vanbiervliet will only be shown in 2040
	global $privacy;
	global $iID;
	// if (!$privacy) {return TRUE;}
	if ($p["I_BIRTH_DATE"]=="") { // No birth date, so not private
		return "ok"; 
	} else {  // there is a birth date
		$currentYear = date_format(new DateTime(), 'Y');
		$d=explode("/", fn_date($p["I_BIRTH_DATE"])); // Does not take into account +/- sign for daughter Karel Ignaas
		if (isset($d[2])) {// Year exists
			if (($currentYear - $d[2]) > 100) {return "ok";} 
		}
		// $d[2] does not exist, check for $d[1];
		else {
			if (isset($d[1])) {		// only month and year, so year=$d[1]
				if (($currentYear - $d[1]) > 100) {return "ok";}
			}
			else {
				if (isset($d[0])) {	// only year
					/*while (!is_numeric($d[0])) {
						$d[0] = trim(substr($d[0], -(strlen($d[0])-1)));
					}*/
					
					//if (($currentYear - $d[0]) > 100) {return "ok";} 
					return "ok";
				}
			}
		}
		return (!$privacy?"mark":"hide");
	}
}

function fn_leeftijd($p) {	// Aangepast om leeftijd levende personen weer te geven (16/12/2021)
	// Changed 19/03/2022: Leeftijd niet tonen als die meer dan 100 jaar is - vb ID=821 
	// Next line: if person not death, do not calculate age. 
	// if (($p["I_BIRTH_DATE"]=="") or ($p["I_DEATH_DATE"]=="")) {return;}
	// But we want to calculate age, also when person is still alive, so we have this line instead, and then calculate age pretending today is day of death
	if ($p["I_BIRTH_DATE"]=="" or $p["I_DEATH_DATE"] == "UNKNOWN") {return;}
	$b = explode("/", fn_date($p["I_BIRTH_DATE"]));
	if ($p["I_DEATH_DATE"]=="") {	// Added to calculate age when alive
		$d=explode(" ",date_format(new DateTime(),'j m Y'));	// $d[0] = 9, $d[1] = 04, $d[2] = 2022
	} else {
		$d = explode("/", fn_date($p["I_DEATH_DATE"]));
	}
	if (isset($d[2])) { // Jaar van dood bestaat, en datum is volledig
		if (isset($b[2])) { // Jaar van geboorte bestaat, en datum is volledig
			if ($b[2]==$d[2]) {
				// minder dan een jaar
				if ($b[1] == $d[1]) { // Minder dan een maand
					if ($b[0] != $d[0]) {$j = $d[0]- $b[0]; return $j. (($j==1)?" dag":" dagen");debug("5");}
					else {return "Gestorven bij de geboorte";debug("6");}
				}
				else {
					$j = $d[1] - $b[1]; 
					if ($d[0] < $b[0]) {$j--;}
					$j2 = ($j==1)?" maand":" maanden";
					return $j.$j2;
				}
			}
			else { // We mogen gewoon de leeftijd berekenen in jaren
				if ($d[1] > $b[1]) {$j = $d[2]-$b[2];}
				if ($d[1] < $b[1]) {$j = $d[2]-1-$b[2];}
				if ($d[1] == $b[1]) {$j = $d[2]-$b[2]; if ($d[0]< $b[0]){$j--;}}
				if ($j == 0) { // Kan alleen als gestorven in jaar na geboorte, maar nog geen jaar oud op dat moment
					//if ($)
				}
				return ($j<101?"$j jaar":"");	// Zorgt ervoor dat niemand 101 jaar of ouder is als sterfdatum niet gekend is
			}
		}
		else { // dood is volledig, geboorte niet
			if (isset($b[1])) { // formaat ofwel ABT 1903, ofwel OCT 1903, ofwel BEF 1903, ofwel AFT 1927 ==> alleen OCT en ABT geval verwerken
				
			}
			else {	// b[1] is niet set
				if (isset($b[0])) {	// alleen jaar, of "voor xxxx" etc
					if (is_numeric($b[0])) {return $d[2]-$b[0]." jaar";}
				}
			}
		}
			
	}
	else { // $d[2] bestaat niet
		if (isset($d[1])) { // formaat ofwel ABT 1903, ofwel OCT 1903, ofwel BEF 1903, ofwel AFT 1927 ==> alleen OCT geval verwerken
			if (($d[1] == "voor") or ($b[1] == "voor") or ($b[1] == "±") or ($d[1] == "±")) {
				return;
			}
			else {
				if (isset($b[2])) { // Geboorte: 01 MAY 1801, Overlijden: OCT 1881 (NIET ABT, BEF of andere niet maanden)
					if ($b[1] < $d[0]) {return $d[2] - $b[1]." jaar";}	// nog aan te passen als jaar hetzelfde is
				}
			}
		}
		else { // maw: ook $d[1] bestaat niet, alleen een jaar dus
			if (isset($b[2])) {
				return $d[0] - $b[2]." jaar";
				
			}
			else {
				if (isset($b[1])) {$gb=$b[1];}
				else {
					if (isset($b[0])) {
						$gb = $b[0];
						return $d[0] - $b[0]." jaar";
					}
				}
			}
		}
	}
}

function f_hw($id1) { // Returns the ID of the husband or wife of $id1. $id1 is assoc. array of individual
	global $conn, $db;
	$a_rel = $id1["I_FAMS_NUMBER"]; //Aantal Relaties
	if ($a_rel != 0) {
		for ($rc = 1; $rc <= $a_rel; $rc++) {
			$famsid = $id1["I_FAMS{$rc}_ID"];
			$family = fn_dosql_all("f", "F_ID", $famsid);
			$pID = ($id1["I_ID"] == $family["F_HUSBAND"])?$family["F_WIFE"]:$family["F_HUSBAND"];
			$pID = ($pID?$pID:"");
		}
	}
	return $pID;
}

function fn_hasfamily($person) { // Returns 1 if there is at least one partner, 2 if there is at least one child, 3 if there is at least one partner and at least one child, or 0 otherwise. $person is associative array, $person["I_FIRST_NAME"] etc.
	global $conn, $db;
	$b = 0;
	$a_partners = $person["I_FAMS_NUMBER"];
	if ($a_partners > 0) {	// A partner and/or children
		if (f_hw($person)!="") {$b=1;} // Has a partner
		$haskids=false;
		for ($d = 1; $d <= $a_partners; $d++) { //$I["I718"]["FS"][1]="F74", $F["F74"]["C"][0]=8
			$famsid = $person["I_FAMS{$d}_ID"];	// $famsid = ID of FAMS1, FAMS2, ... family
			$fam = fn_dosql_all("f", "F_ID", $famsid);
			if ($fam["F_CHILD_NUMBER"] > 0) {$haskids=true;}
		}
		if ($haskids) {$b+=2;}
	}
	return $b;
}

function fn_kids($afamily) {
	//global $I, $F, $a_kids;
	global $conn, $db, $a_kids;
	if ($afamily AND $afamily["F_CHILD_NUMBER"] > 0) {
		$publicKids = 0;
		for ($i = 0; $i < $afamily["F_CHILD_NUMBER"]; $i++) {
			// add my own count of children in case one or more are not public, then do the following lines only when they are public
			$ass = "F_CHILD".$i."_ID";
			$iID = $afamily[$ass];
			$kid = fn_dosql_all("i", "I_ID", $iID);		// $kid is an array, with e.g. $kid["BIRTH_DATE"] etc.
			//if (isPublic($kid)) {	// Oorspronkelijk $i ipv $publicKids in de volgende lijnen
			if ((isPublic($kid) == "ok") or (isPublic($kid) == "mark")) {
				echo "Kids[" . ($publicKids+$a_kids) . "] = \"". $kid["I_FIRST_NAME"]." ". $kid["I_LAST_NAME"]."\";\n";
				echo "KidsID[" . ($publicKids+$a_kids) . "] = \"". $iID."\";\n";
				echo "KidsBD[" . ($publicKids+$a_kids) . "] = \"". fn_DateLine(fn_date($kid["I_BIRTH_DATE"]),fn_date($kid["I_DEATH_DATE"])) ."\";\n";
				echo "KidsK[" . ($publicKids+$a_kids) . "] = \"". fn_hasfamily($kid) ."\";\n";
				$KPStatus = (isPublic($kid) == "mark"?"1":"0");
				echo "KidsColour[" . ($publicKids+$a_kids) . "] = \"".$KPStatus."\";\n";
				echo "KidsPStatus[" . ($publicKids+$a_kids) . "] = \"".isPublic($kid)."\";\n";
				$publicKids++;
			}
		}
		$a_kids = $publicKids;	//$a_kids = $afamily["F_CHILD_NUMBER"];
	}	
}

function fn_parent($fp_id, $who) {	// Return array containing mother or father. If no answer possible, then fn_parent["I_ID"] should be 0
	global $conn, $db;
	$parent="";
	//$parent["I_ID"]=0;    //<= gives an error when php errors are printed. Removed 2021-02-26
	if ($fp_id) {
		$person = fn_dosql_all("i", "I_ID",$fp_id);
		$parentfamid = $person["I_FAMC_ID"];
		$parentfam = fn_dosql_all("f", "F_ID", $parentfamid);	// FAM of parents
		$parentarraystring = ($who == "F"?"F_HUSBAND":"F_WIFE");
		$parentid = $parentfam[$parentarraystring];	// ID of father or mother
		$parent = fn_dosql_all("i", "I_ID", $parentid);
	}
	return $parent;
}

function fn_dosql_all($database, $field, $value) {
	// Return array of selected person or family
	// e.g. fn_dosql_all($i, "I_ID", 746)	=> Returns array from individual table where I_ID = 746
	global $conn, $db;
	if (!$value) {return 0;}
	if (strtolower($database) == "i") {$database="individual";}
	if (strtolower($database) == "f") {$database="family";}
	$result = mysqli_query($conn, "SELECT * FROM $db.$database WHERE $field=$value;");
	if (mysqli_error($conn)) {
		//echo "Error: ".mysqli_error($conn)."\n";
		//echo "Offending SQL statement: SELECT * FROM $db.$database WHERE $field=$value;";
		//die;
		return NULL;
	}
	else {
		return mysqli_fetch_assoc($result);
	}
}

function fn_date($di){
	// Mogelijke formaten: leeg, 23 OCT 1970, 1821, ABT 1903, ...
	$dp = explode(" ", $di);
	$glue = " ";
	if (isset($dp[1]) and isset($dp[0])) {
		$months = array("JAN" => "01", "FEB" => "02", "MAR" => "03", "APR" => "04", "MAY" => "05", "JUN" => "06", "JUL" => "07", "AUG" => "08", "SEP" => "09", "OCT" => "10", "NOV" => "11", "DEC"=> "12");
		for ($dpi=0; $dpi<=1;$dpi++) {
			if (array_key_exists($dp[$dpi], $months)) {
				$tdp = $months[$dp[$dpi]]; 
				$dp[$dpi] = $tdp;
				$glue = "/";
			}
		}
		$di = implode($glue, $dp);
	}
	if (isset($dp[0])) {
		$specials = array("BEF" => "voor", "ABT" => "±", "AFT" => "na");
		if (array_key_exists($dp[0], $specials)) {
			$tdp = $specials[$dp[0]]; 
			$dp[0] = $tdp;
			$glue = " ";
		}
		$di = implode($glue, $dp);
	}
	return $di;
}

function fn_DateLine($iG, $iD) {
	$r="";
	if (isset($iG) and (trim($iG)!= "")) { // Er is _IETS_ in geboorte, en niet gewoon spaties
		$r = "° ".$iG;
		if (isset($iD) and (trim($iD)!="")) {
			$iD = ($iD=="UNKNOWN")?"?":$iD; // To change Unknown into "?"
			$r = $r ." - † ".$iD;
		}
	}
	else { //Geen geboorte data
		if (isset($iD) and (trim($iD)!="")) { // Maar wel sterfdatum
			$r = "? - † ".$iD;
		}
	}
	return $r;
}

function getCurrentDb($conn, $myTree) { // Return the database if it exists, "false" otherwise
	mysqli_select_db($conn, "trees");
	if (mysqli_errno($conn)) {return false;}	// No "trees" db
	else {	// DB Trees exists, select that db
		//$sql = "SELECT T_T_version FROM trees.tree_info WHERE T_T_name = '$myTree' AND T_T_status = 'active';";
		$result = mysqli_query($conn, "SELECT T_T_version FROM trees.tree_info WHERE T_T_name = '$myTree' AND T_T_status = 'active';");
		if (mysqli_num_rows($result) == 0) {return false;}
		if (mysqli_num_rows($result) == 1) {
			$row = mysqli_fetch_assoc($result);
			return $row["T_T_version"];
		}
		return false;
	}
}

function debug($text) {
	echo "console.log(\"$text\");\n";
}
?>

</script></head>
<body style="background-color:#ccccff">

<script type="text/javascript" src="stamboom.js"></script>
<script type="text/javascript">drawstamboom();</script>

<!--
Stamboom versie 0.80
2 April 2022
De infobox wordt nu standaard getoond
Huwelijksgegevens worden getoond indien beschikbaar
Correcties in het berekenen van de leeftijd

Stamboom versie 0.71
19 Maa 2022
Personen zonder sterfdatum hebben een maximum leeftijd van 100 jaar (om te vermijden dat voor sommigen 270 jaar vermeld wordt)

Stamboom versie 0.70<p>
06 Maa 2022 versie 0.70
Gegevens van personen die minder dan 100 jaar (zouden) zijn, zijn alleen zichtbaar voor gebruikers die ingelogd zijn

Stamboom versie 0.62
21 Dec 2021 Versie 0.62
Infobox toont ook leeftijd van mensen die nog leven. 

Stamboom versie 0.61<p>
19 Feb 2017 Versie 0.61
<ul>
<li>Persoonsinformatie toont nu ook beroep en alias</li>
</ul>

19 Feb 2017 Versie 0.60
<ul>
<li>Stamboom via SQL</li>
</ul>

17 Jan 2017 Versie 0.56
<ul>
<li>Unified stamboom</li>
</ul>

13 Jan 2017 Versie 0.55:
<ul>
<li>Kinderen kunnen nu een pijltje hebben. Geen pijltje betekent geen partner en geen afstamming. Wit pijlte is alleen een partner. Grijs pijltje is alleen kinderen. Zwart is partner en kinderen.</li>
</ul>
Beperkingen:<br>
<ul>
<li><strike>Stamboom zelf is niet volledig</strike> De stamboom is gebaseerd op de laatste volledige stamboom, maar vertoont ongetwijfeld nog hiaten. Laat gerust weten wat niet klopt zodat ik het kan aanpassen.</li>
<li><strike>Er worden alleen nog maar namen getoond, geen andere gegevens</strike> <strike>Namen en geboorte- en sterfdata worden getoond. De gegevens over de persoon, zoals geboorteplaats, beroep, etc. moeten in een apart kader komen.</strike></li>
<ul><li>5 Jan 2017: Om wat meer gegevens te zien van de hoofdpersoon (de persoon in het oranje-rode kader), klik op het kader.</li></ul>
<ul><li>5 Jan 2017: <strike>Op het kader klikken werkt niet. Geen idee waarom niet.</strike> Opgelost. Had te maken met https bestand dat verwijst naar http link voor css en js.</li></ul>
<li><strike>Er worden nog geen partners getoond</strike></li>
<li>In geval van meerdere partners zijn er nog problemen <strike>(alleen kinderen met 1 van de partners worden getoond</strike>, <strike>er wordt maar 1 partner getoond, ...). </strike>
<ul><li>Beperkt tot 2 partners, maar zijn er gevallen met meer? Bekendste geval met kinderen bij twee partners is <a href="?tree=vanbiervliet&ID=736">Ignaes Vanbiervliet</a></li>
<li>Update 13 Jan 2017: <a href="?tree=vanbiervliet&ID=1559">Ludovicus Vercruysse</a> had drie partners.</li></ul>
<li><strike>Er blijkt een probleem te zijn bij <em><a href="?tree=vanbiervliet&ID=428">Diederik Van Komen</a></em></strike> Probleem was een onvoorziene omstandigheid van een persoon met minstens 1 kind, maar zonder partner.</li>
<li><strike>Sommige namen zijn nog altijd te lang</strike></li>
</ul>
Wishlist<ul><li>Zoekfunctie</li><li>Aanduiding weg naar proband (afstamming, voorouders, ...)</li><li>Gebruiker zelf proband laten kiezen (momenteel hardcoded op <a href="?tree=vanbiervliet&ID=718">Miel Smet</a>)</li><li>Gebruiker historie bijhouden om makkelijker op z'n stappen terug te laten keren</li>
<li>php en js vereenvoudigen (voornamelijk kortere notaties voor arrays gebruiken)</li><li>Gebruiker kleuren laten kiezen</li><li><strike>SQL ipv php db</strike></li><li><strike>Autenticatie om privacy gevoelige gegevens af te schermen</strike></li></ul>
-->
</body>
</html>
