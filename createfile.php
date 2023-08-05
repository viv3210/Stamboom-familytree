<!DOCTYPE html>
<html>
<head>
<script>
function suggestDBName() {
    var x = document.forms["myForm"]["inputfile"].value;
    if (x != "") {
		var x1 = x.lastIndexOf("\\");
		var x2 = x.indexOf(".");
		var dbsug = x.substr(x1+1,x2-x1-1);
		if (dbsug.indexOf("_") != 0) {
			dbsug = dbsug.substring(0, dbsug.indexOf("_"));
		}
		document.forms["myForm"]["db"].value = dbsug.toLowerCase();
    }
}

</script></head>
<body>
<?php
if ($_SERVER["REQUEST_METHOD"] != "POST") {
	echo "<form name=\"myForm\" method=\"post\" action=\"".htmlspecialchars($_SERVER["PHP_SELF"])."\">\n";
	echo "GED File: <input type=\"file\" name=\"inputfile\" onChange=\"suggestDBName()\">\n";
	echo "<p>\n";
	echo "Database: <input type=\"text\" name=\"db\">\n";
	echo "<p>\n";
	echo "<input type=\"submit\">\n";
	echo "</form>\n";
}

// define variables and set to empty values
$db = $inputfile = "";
$good="<font color=\"blue\">";
$goodend="</font>";
$bad="<font color=\"red\"><b>";
$badend="</b></font>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $dbtype = test_input(strtolower($_POST["db"]));
  $inputfile = test_input($_POST["inputfile"]);
  echo "GED file: $inputfile<p>\n";
  echo "Database type: $dbtype<p>\n";
  // generate timestamp
  $stamp = date("YmdHis");
  $db=$dbtype.$stamp;
  echo "Database: $db<p>\n";
  connectToDBServer($db, $dbtype);
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function connectToDBServer($t_db, $t_dbtype) {
	global $inputfile;
	global $bad;
	global $badend;
	global $good;
	global $goodend;
	include '../files/db.php';
	$conn = mysqli_connect($servername, $username, $password);
	//$conn = mysqli_connect();	// php.ini file has all connection info
	if (!$conn) {echo $bad."Cannot connect to database. Bye!".$badend;die;}
	
	echo $good."Successfully connected to MySQL database server.".$goodend."<p>\n";
	mysqli_select_db($conn, "trees");
	if (mysqli_errno($conn)) {create_db_trees($conn);}
	else {	// DB Trees exists, select that db
		echo "Database Trees already exists, connecting to database Trees!<p>\n";
		$result = mysqli_select_db($conn, "trees");	// If trees exists the result is "1", otherwise it is empty
		if ($result) {echo "Connected to table tree_info.<p>\n";}
		else {echo "Problem connecting to table tree_info. Quiting now.";die;}
	}
	$my_db = add_new_db($conn, $t_dbtype, $t_db);
	create_new_treedb($conn, $my_db);
	doStuff($conn, $t_db, $inputfile); // do stuff here, i.e. fill db with data
	change_db_status($conn, $t_dbtype);

	$close = mysqli_close($conn);
	if ($close){echo "\nMySQL connection closed successfully.\n<br>";}
	else{echo "There's a problem in closing MySQL connection.\n<br>";}
}

function create_db_trees($myconn) {
	$sql = "CREATE DATABASE trees";
	if (mysqli_query($myconn, $sql)) {
		echo "Database created successfully<p>\n";
		$sql = "CREATE TABLE tree_info (
			T_T_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
			T_T_name VARCHAR(30) NOT NULL,
			T_T_version VARCHAR(30) NOT NULL,
			T_T_status VARCHAR(50) NOT NULL
		)";
		mysqli_select_db($myconn, "trees");
		if (mysqli_query($myconn, $sql)) {
			echo "Tables created.<p>\n";
		}
		else {
			echo "Problem creating tables. Error ". mysqli_error($myconn) . "<p>\n";
		}
	} else {
		echo "Error creating database trees: " . mysqli_error($myconn) . "<p>\n";
	}
}

function change_db_status($conn, $dbtype) {
	/* There are four types of status: obsolete, previous, active, and future
	   Goal:	previous -> obsolete (may be deleted)
				active -> previous
				future -> active
	*/
	$sql = "UPDATE trees.tree_info SET T_T_status='obsolete' WHERE T_T_status='previous' AND T_T_name='$dbtype'";
	$result = mysqli_query($conn, $sql);
	echo "Changing previous to obsolete. Result: $result<p>\n";
	
	$sql = "UPDATE trees.tree_info SET T_T_status='previous' WHERE T_T_status='active' AND T_T_name='$dbtype'";
	$result = mysqli_query($conn, $sql);
	echo "Changing active to previous. Result: $result<p>\n";
	
	$sql = "UPDATE trees.tree_info SET T_T_status='active' WHERE T_T_status='future' AND T_T_name='$dbtype'";
	$result = mysqli_query($conn, $sql);
	echo "Changing future to active. Result: $result<p>\n";
}

function add_new_db($conn, $t_dbtype, $t_db) {
		// Db and table are there, insert data
		// First check if there is already one with 'future' as status, and see if that is an error
		$my_db = check_future_db($conn,$t_dbtype, $t_db);
		if ($my_db == $t_db){
			echo "No database with 'future' <p>\n";
			echo "Db and table are there, insert data<p>\n";	
			$sql = "INSERT INTO tree_info (T_T_name, T_T_version, T_T_status) VALUES ('$t_dbtype', '$t_db', 'future')";
			if (mysqli_query($conn, $sql)) {
				echo "New record created successfully\n\n";
				return $t_db;
			} else {
				echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			}
		}
		else {
			echo "Er is al een database met status 'future', namelijk $my_db<p>\n";
		}
}

function check_future_db($conn, $t_dbtype, $t_db) { //Returns false if there is a db of the type $db_type marked as 'future'
	echo "Checking if there is already a database with the name <i>$t_dbtype</i> registered as future<p>\n";
	$sql = "SELECT * FROM tree_info WHERE T_T_status = 'future'";
	$result = mysqli_query($conn, $sql);
	if (mysqli_num_rows($result) == 0) {return $t_db;}
	if (mysqli_num_rows($result) == 1) {
		$row = mysqli_fetch_assoc($result);
		return $row["T_T_version"];
	}
	if (mysqli_num_rows($result) > 1) {echo "Teveel database met status 'future'. Ik hou er mee op.<p>\n";die;}
}

function create_new_treedb($conn, $t_db) {
	// Create a database to store the tree, for example vanbiervliet20170204201300
	// Mysql datatypes see http://www.htmlite.com/mysql003.php
	$sql = "CREATE DATABASE $t_db";
	if (mysqli_query($conn, $sql)) {
		echo "Database $t_db created successfully<p>\n";
		mysqli_select_db($conn, $t_db);
		$sql = "CREATE TABLE INDIVIDUAL (
			I_ID 					SMALLINT UNSIGNED PRIMARY KEY,
			I_FIRST_NAME			VARCHAR(50),
			I_ALIAS					VARCHAR(45),
			I_LAST_NAME				VARCHAR(45),
			I_SEX					VARCHAR(45),
			I_BIRTH_DATE			VARCHAR(45),
			I_BIRTH_PLACE			VARCHAR(75),
			I_BAPTISM_DATE			VARCHAR(45),
			I_BAPTISM_PLACE			VARCHAR(45),
			I_BAPTISM_GODFATHER		VARCHAR(45),
			I_BAPTISM_GODMOTHER		VARCHAR(45),
			I_DEATH_DATE			VARCHAR(45),
			I_DEATH_PLACE			VARCHAR(70),
			I_FUNURAL_DATE			VARCHAR(45),
			I_FUNURAL_PLACE			VARCHAR(45),
			I_BURIAL_DATE			VARCHAR(45),
			I_BURIAL_PLACE			VARCHAR(45),
			I_OCCUPATION			VARCHAR(130),
			I_FAMC_ID				INT UNSIGNED,
			I_FAMS_NUMBER			SMALLINT UNSIGNED,
			I_FAMS1_ID				SMALLINT UNSIGNED,
			I_FAMS2_ID				SMALLINT UNSIGNED,
			I_FAMS3_ID				SMALLINT UNSIGNED,
			I_FAMS4_ID				SMALLINT UNSIGNED,
			I_NOTE					VARCHAR(45),
			F_WEDDING_CHURCH_DATE	VARCHAR(45),
			F_WEDDING_CHURCH_PLACE	VARCHAR(45)
		)";
		if (mysqli_query($conn, $sql)) {
			echo "Tables <b>Individual</b> created.<p>\n";
		}
		else {
			echo "Problem creating table <b>Individual</b>. Error ". mysqli_error($conn) . "<p>\n";
		}
		$sql = "CREATE TABLE FAMILY (		
			F_ID				SMALLINT UNSIGNED PRIMARY KEY,
			F_HUSBAND			SMALLINT UNSIGNED,
			F_WIFE				SMALLINT UNSIGNED,
			F_MARRIAGE_DATE		VARCHAR(45),
			F_MARRIAGE_PLACE	VARCHAR(75),
			F_CHILD_NUMBER		SMALLINT UNSIGNED,
			F_CHILD0_ID			SMALLINT UNSIGNED,
			F_CHILD1_ID			SMALLINT UNSIGNED,
			F_CHILD2_ID			SMALLINT UNSIGNED,
			F_CHILD3_ID			SMALLINT UNSIGNED,
			F_CHILD4_ID			SMALLINT UNSIGNED,
			F_CHILD5_ID			SMALLINT UNSIGNED,
			F_CHILD6_ID			SMALLINT UNSIGNED,
			F_CHILD7_ID			SMALLINT UNSIGNED,
			F_CHILD8_ID			SMALLINT UNSIGNED,
			F_CHILD9_ID			SMALLINT UNSIGNED,
			F_CHILD10_ID		SMALLINT UNSIGNED,
			F_CHILD11_ID		SMALLINT UNSIGNED,
			F_CHILD12_ID		SMALLINT UNSIGNED,
			F_CHILD13_ID		SMALLINT UNSIGNED,
			F_CHILD14_ID		SMALLINT UNSIGNED,
			F_CHILD15_ID		SMALLINT UNSIGNED,
			F_CHILD16_ID		SMALLINT UNSIGNED,
			F_CHILD17_ID		SMALLINT UNSIGNED,
			F_CHILD18_ID		SMALLINT UNSIGNED,
			F_CHILD19_ID		SMALLINT UNSIGNED
		)";
		if (mysqli_query($conn, $sql)) {
			echo "Tables <b>Family</b> created.<p>\n";
		}
		else {
			echo "Problem creating table <b>Family</b>. Error ". mysqli_error($conn) . "<p>\n";
		}		
	} else {
		echo "Error creating database $t_db: " . mysqli_error($conn) . "<p>\n";
	}	
}

function doStuff($conn, $db, $inputfile) { 
	// Will be executed when there is a valid file and db. 
	// Create db, create tables, add the info stripped from the GED file into the db. 
	// Drop tables if they exist and rewrite (first create temp db, rename original db, rename temp db)
	// Way to test: generate random string (or date+timetamp), write into temp db, and after renaming db, read it again

	$handle = fopen($inputfile, "r");
	$individunummer=0; //$individunummer => 1 = vincent, 2 = ?, 3 = Ali...
	$famnr=0; //familienummer
	$bbirth = FALSE; $bdeath = FALSE; $bGM = FALSE; $bGF = FALSE; $bFam = FALSE; $boccu = FALSE;			// Booleans. bGM=godmother, bGF=godfather
	$bmarr = FALSE;

	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			// Split line by spaces (use 'explode' function, then array_map to trim all array items)
			$line = utf8_encode($line);
			$lineitem = array_map('trim',explode(" ", $line));
			//$linewords = count($lineitem);

			// Reset variablelen
			$namen = ""; $voornamen = ""; $sex = ""; 
			
			
			// ******************************
			// INDIVIDU informatie verzamelen
			// ******************************
			
			if (isset($lineitem[2])) {
				if ($lineitem[2]=="INDI") { // Begin nieuw indidvidu, dus persoonsnummer bepalen. Vb; "0 @I1@ INDI" is begin van Persoon 1 ==> Update voor database: Individunummer niet meer I124, maar 124. Zelfde voor familienummer
					// $individunummer = fn_sc($lineitem[1],"@");	//formaat: I1
					$individunummer = fn_sc_no($lineitem[1],"@");	//formaat: 1 ==> Database
					$I[$individunummer]["Voornaam"] = ""; 		//Voor het geval die niet bestaat
					$I[$individunummer]["Naam"] = "";			//Voor het geval die niet bestaat
					$I[$individunummer]["Sex"] = "";			//Voor het geval die niet bestaat
					$I[$individunummer]["Birth"]["Date"] = "";	//Voor het geval die niet bestaat
					$I[$individunummer]["Birth"]["Place"] = "";	//Voor het geval die niet bestaat
					$I[$individunummer]["Birth"]["GF"] = "";	//Voor het geval die niet bestaat
					$I[$individunummer]["Birth"]["GM"] = "";	//Voor het geval die niet bestaat
					$I[$individunummer]["Death"]["Date"] = "";	//Voor het geval die niet bestaat
					$I[$individunummer]["Death"]["Place"] = "";	//Voor het geval die niet bestaat
					$I[$individunummer]["FAMS"][0] = "0";		//Voor het geval die niet bestaat ==> We gaan uit van 0 FAMS'en
					$I[$individunummer]["FAMC"] = "0";			//Voor het geval die niet bestaat
					$I[$individunummer]["Occupation"] = "";		//Voor het geval die niet bestaat
					$I[$individunummer]["Alias"] = "";			//Voor het geval die niet bestaat
					$fc = 0;	// Family counter, analoog aan child counter

				}
				if ($lineitem[2]=="FAM") { // Begin nieuw familie, dus familienummer bepalen. Vb; "0 @F1@ FAM" is begin van Familie 1
					$bmarr = FALSE;
					if (!strpos(fn_sc($lineitem[1],"@"),"-")) {	// Toegevoegd 7-1-2017 nav probleem met Marie Henriette, fa Jules fs Henri van 't Kanon
						$bFam = TRUE;
						//$famnr = fn_sc($lineitem[1],"@");	// Formaat F1
						$famnr = fn_sc_no($lineitem[1]);		// Formaat 1 ==> Database
						$cc = 0; //$child counter for this family
						$F[$famnr]["Husband"] = "";
						$F[$famnr]["Wife"] = "";
						$F[$famnr]["Child"][0] = 0;
						$F[$famnr]["Marriage"]["Date"] = "";
						$F[$famnr]["Marriage"]["Place"] = "";
					}
				}
			}
			// Naam toevoegen aan array, alleen als individunummer <> 0
			if ($individunummer != "0") {  
				if (isset($lineitem[1])){ // We hebben een tweede woord
					if ($lineitem[1]=="NAME") { //Naam en voornaam bepalen
						
						$namen = substr($line, 7);
						$posFamilienaam = strpos($namen, "/");
						if ($posFamilienaam) { // Er is een voornaam
							// $voornamen = substr($namen, 0, $posFamilienaam - 1);	//Volgende lijn toegevoegd omdat de naam soms "Rachel /Vanbiervliet/" is, en soms "Rachel/Vanbiervliet/" (1e geval: family tree maker, 2e geval: Aldfaer)
							$voornamen = trim(substr($namen, 0, $posFamilienaam));
						}
						$I[$individunummer]["Voornaam"] = $voornamen;
											
						// Familienaam bepalen tussen twee / tekens
						$familienaam1 = substr($namen, $posFamilienaam+1, strlen($namen) - $posFamilienaam - 4);
						$I[$individunummer]["Naam"] = $familienaam1;
					}
					
					if ($lineitem[1] =="SEX") { //Geslacht bepalen
						$I[$individunummer]["Sex"] = $lineitem[2]; //Mogelijkheden: F, M of U
					}

					if ($lineitem[1] == "FAMC") { // 1 FAMC @F13@
						// $lineitem[2] is de familie voor de FAMC
						// FAMC is ouderlijk gezin
						$f_id = trim(fn_sc($lineitem[2],"@"));
						//if (isset($f_id)) {$I[$individunummer]["FAMC"] = $f_id;}
						if (isset($f_id)) {$I[$individunummer]["FAMC"] = substr($f_id,1);}	// Database
					}
					
					if ($lineitem[1] == "FAMS") { // 1 FAMS @F13@
						if (!strpos($lineitem[2],"-")) { // Verwijderde persoon. Denk ik. Toegevoegd 7-1-2017 voor probleem Marie Henriette
							// $lineitem[2] is de familie voor de FAMS
							// FAMS is eigen familie, kan dus meer dan 1 zijn
							$I[$individunummer]["FAMS"][0] = ++$fc;
							$f_id = trim(fn_sc($lineitem[2],"@"));
							//if (isset($f_id)) {$I[$individunummer]["FAMS"][$fc] = $f_id;}
							if (isset($f_id)) {$I[$individunummer]["FAMS"][$fc] = substr($f_id,1);} //Database
						}
					}
					
					// Geboorte checken
					// $bbirth = boolean voor geboorte, maw zijn we in de geboortegegevens of niet
					// $bbirth uitzetten indien $lineitem[0] = 1 is, en $bbirth waar was
					// $bbirth aanzetten indien $lineitem[1] = BIRT

					$bbirth = ((($lineitem[0]=="1") AND ($bbirth))?FALSE:$bbirth); //Uitzetten
					$bbirth = (($lineitem[1]=="BIRT")?TRUE:$bbirth); //Aanzetten
					
					if ($bbirth) {	//Geboortedatum en -plaats bepalen
						if ($lineitem[1] == "DATE") {
							$t_gd = substr($line,7, strlen($line) - 4);
							$I[$individunummer]["Birth"]["Date"] = trim($t_gd);
						}
						if ($lineitem[1] == "PLAC") {
							$t_gp = substr($line,7, strlen($line) - 4);
							$I[$individunummer]["Birth"]["Place"] = trim($t_gp);
						}

					}
					
					$bdeath = ((($lineitem[0]=="1") AND ($bdeath))?FALSE:$bdeath); //Uitzetten
					$bdeath = (($lineitem[1]=="DEAT")?TRUE:$bdeath); //Aanzetten
					
					if ($bdeath) {	//Sterfdatum en -plaats bepalen
						if ($lineitem[1] == "DATE") {
							$t_sd = substr($line,7, strlen($line) - 4);
							$I[$individunummer]["Death"]["Date"] = trim($t_sd);
						}
						if ($lineitem[1] == "PLAC") {
							$t_sp = substr($line,7, strlen($line) - 4);
							$I[$individunummer]["Death"]["Place"] = trim($t_sp);
						}
					}

					// Peter en meter checken, en in I[$id]["Birth"]["GM"] en -["GF"] steken
					$bGM = ((($lineitem[0]!=="2") AND ($bGM))?FALSE:$bGM); //Uitzetten
					$bGF = ((($lineitem[0]!=="2") AND ($bGF))?FALSE:$bGF); //Uitzetten
					if (isset($lineitem[2])) {$bGM = (($lineitem[2]=="Godmother")?TRUE:$bGM);} //Aanzetten}
					if (isset($lineitem[2])) {$bGF = (($lineitem[2]=="Godfather")?TRUE:$bGF);} //Aanzetten}
					
					if ($bGM OR $bGF) {
						if ($lineitem[1]="PLAC") {
							if (isset($lineitem[2])) {
								$t_gfm = ($bGM?"GM":"GF");
								$I[$individunummer]["Birth"][$t_gfm] = trim(substr($line,7));
							}
						}
					}
					
					// Beroep toevoegen
					$boccu = ((($lineitem[0]=="1") AND ($boccu))?FALSE:$boccu); //Uitzetten
					$boccu = (($lineitem[1]=="OCCU")?TRUE:$boccu); //Aanzetten
					
					if ($boccu) {
						if ($lineitem[1] =="OCCU") { //Beroep
							$I[$individunummer]["Occupation"] = $lineitem[2];
							$words = count($lineitem);
							if ($words > 3) {
								for ($v = 3; $v < $words; $v++) {
									$I[$individunummer]["Occupation"] .= " ".$lineitem[$v];
								}
							}
						}
						if ($lineitem[1] =="CONC") { //Vervolg op beroep
							$I[$individunummer]["Occupation"] .= $lineitem[2];
							$words = count($lineitem);
							if ($words > 3) {
								for ($v = 3; $v < $words; $v++) {
									$I[$individunummer]["Occupation"] .= " ".$lineitem[$v];
								}
							}
						}
						
					}
					
					/*
					if ($lineitem[1] =="OCCU") { //Beroep
						$I[$individunummer]["Occupation"] = $lineitem[2];
						$words = count($lineitem);
						if ($words > 3) {
							for ($v = 3; $v < $words; $v++) {
								$I[$individunummer]["Occupation"] .= " ".$lineitem[$v];
							}
						}
					}
					*/
					if ($lineitem[1] =="ALIA") { //Alias
						$I[$individunummer]["Alias"] = $lineitem[2];
						$words = count($lineitem);
						if ($words > 3) {
							for ($v = 3; $v < $words; $v++) {
								$I[$individunummer]["Alias"] .= " ".$lineitem[$v];
							}
						}
					}

				}
			}

			// *****************************
			// FAMILIE informatie verzamelen
			// *****************************
			if ($bFam) { // We're working with families. Current familynumber: $famnr
				if (isset($lineitem[2])) { // We can check HUSB, WIFE, CHIL, and others
					switch ($lineitem[1]) {
						case "HUSB":
							//$F[$famnr]["Husband"] = fn_sc($lineitem[2],"@");
							$F[$famnr]["Husband"] = fn_sc_no($lineitem[2],"@");	// Database
							break;
						case "WIFE":
							//$F[$famnr]["Wife"] = fn_sc($lineitem[2],"@");
							$F[$famnr]["Wife"] = fn_sc_no($lineitem[2],"@");	// Database
							break;
						case "CHIL":
							// Pseudocode *****
							// $cc (childcounter)
							// bij 0 @F... $cc=0 (ook als er geen kinderen zullen zijn)
							// bij 1 CHIL: 
							//     $F[$fn]["Child"][$cc]=I123 => Eerste kind index 0, ...
							//     $cc++ (wordt 1 voor eerste kind, 2 voor tweede kind, ...)
							// indien opnieuw 0 @F, $cc wordt opnieuw 0
							if (!strpos($lineitem[2],"-")) { // Verwijderde persoon. Denk ik. Toegevoegd 7-1-2017 voor probleem Marie Henriette
								$cc++;	// bij eerste kind wordt dit dus = 1, enz.
								//$F[$famnr]["Child"][$cc] = fn_sc($lineitem[2],"@");
								$F[$famnr]["Child"][$cc] = fn_sc_no($lineitem[2],"@");	// Database
								$F[$famnr]["Child"][0] = $cc; // Geeft het totaal aantal kinderen weer, indien geen kinderen bestaat die variabele niet. Zou eigenlijk 0 moeten zijn
							}
							break;
					}
				}
				//Check marriage
				/* new for marriage date/place 27-03-2022 */
				if ($lineitem[0]=="1") {
					$bmarr = ($lineitem[1]=="MARR"?TRUE:FALSE);	// when $bmarr is true then we are inside a marriage block, e.g. 2 _MREL Natural; 1 marr; 2 date 12 sep 1969; 2 plac brugge; 0 @F39@ FAM
				}
				if ($bmarr) {
					if ($lineitem[1] == "DATE") {
						$t_md = substr($line,7, strlen($line) - 4);
						$F[$famnr]["Marriage"]["Date"] = trim($t_md);
					}
					if ($lineitem[1] == "PLAC") {
						$t_mp = substr($line,7, strlen($line) - 4);
						$F[$famnr]["Marriage"]["Place"] = trim($t_mp);
					}
				}
				/* end new */
			}
		}
		
		fclose($handle);
	} else {
		echo "Error reading file $inputfile\n";
	} 
	
		$ikeys = array_keys($I); // ==> $keys[0] = "1" (niet meer I1 omwille van database)
		$fouten = 0;
		$a_ikeys = count($ikeys);
		for ($c = 0; $c < $a_ikeys; $c++) {
			$t_iid = $ikeys[$c];
			$sqlstatement = $t_iid.",";
			$sqlstatement .= "'".mysqli_real_escape_string($conn, $I[$t_iid]["Voornaam"])."', ";
			$sqlstatement .= "'".mysqli_real_escape_string($conn, $I[$t_iid]["Naam"])."', ";
			$sqlstatement .= "'{$I[$t_iid]["Sex"]}', ";
			$sqlstatement .= "{$I[$t_iid]["FAMC"]}, ";
			$a_c = $I[$t_iid]["FAMS"][0];
			$sqlstatement .= "$a_c, ";
			//$outstring .= ";\$I[\"$t_iid\"][\"FS\"][0]=$a_c";
			$colums = "I_ID, I_FIRST_NAME, I_LAST_NAME, I_SEX, I_FAMC_ID, I_FAMS_NUMBER,";
			// Vb van een familie:
			// $F["F39"]["H"]="I699";$F["F39"]["W"]="I78";$F["F39"]["C"][0]=2; $F["F39"]["C"][1]="I1"; $F["F39"]["C"][2]="I815"; ==> De kinderen zijn I1 en I815 (Vincent en Philippe, hier al in volgorde)
			
			if ($a_c > 0) {
				for ($c_f = 1; $c_f <= $a_c; $c_f++) {
					//$fso = str_replace("-","",$I[$t_iid]["FAMS"][$c_f]);
					//$outstring .= "; \$I[\"$t_iid\"][\"FS\"][$c_f]=\"{$I[$t_iid]["FAMS"][$c_f]}\"";
					//$outstring .= "; \$I[\"$t_iid\"][\"FS\"][$c_f]=\"".$fso."\"";
					$sqlstatement.= "{$I[$t_iid]["FAMS"][$c_f]}, ";
					$colums.="I_FAMS".$c_f."_ID, ";
				}
			}		

			$sqlstatement .= "'{$I[$t_iid]["Birth"]["Date"]}', ";
			$sqlstatement .= "'".mysqli_real_escape_string($conn,$I[$t_iid]["Birth"]["Place"])."', ";
			$sqlstatement .= "'{$I[$t_iid]["Birth"]["GF"]}', ";
			$sqlstatement .= "'{$I[$t_iid]["Birth"]["GM"]}', ";
			$sqlstatement .= "'{$I[$t_iid]["Death"]["Date"]}', ";
			$sqlstatement .= "'".mysqli_real_escape_string($conn,$I[$t_iid]["Death"]["Place"])."', ";
			$sqlstatement .= "'".mysqli_real_escape_string($conn,$I[$t_iid]["Occupation"])."', ";
			$sqlstatement .= "'".mysqli_real_escape_string($conn,$I[$t_iid]["Alias"])."'";
			$colums .= "I_BIRTH_DATE, I_BIRTH_PLACE, I_BAPTISM_GODFATHER, I_BAPTISM_GODMOTHER, I_DEATH_DATE, I_DEATH_PLACE, I_OCCUPATION, I_ALIAS";
			$sql = "INSERT INTO $db.individual ($colums) VALUES ($sqlstatement)";
			if (!mysqli_query($conn, $sql)) {echo "Error: " . $sql . "<br>" . mysqli_error($conn) . "<p>\n";$fouten++;}
		
			//echo $t_iid==1?"$sql<p><p>\n":'';	// Writes SQL statement for ID=1
		}
		echo "Aantal fouten (Individu tabel): $fouten<p>\n";
		$outstring = "";
		$fkeys = array_keys($F); // ==> $keys[0] = "F1"
		$fouten=0;
		$a_fkeys = count($fkeys);
		
		for ($c_f = 0; $c_f < $a_fkeys; $c_f++) {
			$t_fid = $fkeys[$c_f];
			if ($F[$t_fid]["Husband"] == "") {$F[$t_fid]["Husband"] = 0;}
			if ($F[$t_fid]["Wife"] == "") {$F[$t_fid]["Wife"] = 0;}
			$sqlstatement = $t_fid.", ";
			$sqlstatement .= "{$F[$t_fid]["Husband"]}, ";
			$sqlstatement .= "{$F[$t_fid]["Wife"]}, ";
			$sqlstatement .= "\"{$F[$t_fid]["Marriage"]["Date"]}\", ";
			$sqlstatement .= "\"{$F[$t_fid]["Marriage"]["Place"]}\", ";
			
			$a_k = $F[$t_fid]["Child"][0];
			$sqlstatement .= "$a_k";
			//$colums = "F_ID, F_HUSBAND, F_WIFE, F_CHILD_NUMBER";
			$colums = "F_ID, F_HUSBAND, F_WIFE, F_MARRIAGE_DATE, F_MARRIAGE_PLACE, F_CHILD_NUMBER";
			if ($a_k > 0) { // Kinderen zouden hier in volgorde moeten gezet worden
				// Array van kinderen maken
				for ($c_c = 1; $c_c <= $a_k; $c_c++) {
					$tempID = $F[$t_fid]["Child"][$c_c];
					if (isset($I[$tempID]["Birth"]["Date"])) {$tempBirthYear = fn_getyear($I[$tempID]["Birth"]["Date"]);}
					else {$tempBirthYear = "0";}
					$Kid[$tempID] = $tempBirthYear;
				}
				// E.g. $Kid(I1 => 1970, I815 => 1971)
				asort($Kid);
				$Kidsids = array_keys($Kid);
				for ($c_c = 1; $c_c <= $a_k; $c_c++) {
					$kn = $c_c -1;
					$kfi = $Kidsids[$kn];
					$sqlstatement .= ", $kfi";
					$colums .= ", F_CHILD{$kn}_ID";
				}
				unset($Kid);
			}
			$sql = "INSERT INTO $db.family ($colums) VALUES ($sqlstatement)";
			if (!mysqli_query($conn, $sql)) {echo "Error: " . $sql . "<br>" . mysqli_error($conn) . "<p>\n";$fouten++;}
		}
		echo "Aantal fouten (Family tabel): $fouten<br>\n\n";
		echo "Succes<p><p>";
		echo date(DATE_RFC2822);
}	

function fn_sc($a,$b) { //function strip character. e.g. fn_sc("@I102@","@") returns "I102"
	return implode("",explode($b,trim($a)));
}
function fn_sc_no($a) {return substr(fn_sc($a,"@"),1);}

function fn_getyear($y) {
	if (strrpos($y, " ")) {$r = trim(substr($y, strrpos($y, " ")));}
	else {$r = trim($y);}
	return $r?$r:"0";
}

?>

</body>
</html>
