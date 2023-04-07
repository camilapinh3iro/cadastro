<?php
DEFINE("CONF_BUILD", "OZY's CAPTIVE PORTAL FOR RADIUS/MySQL authentication conf 2016111701");
// Config file for captive portal

/************************************* TEST ENV */
/*
DEFINE("DEBUG", true);
DEFINE("DBHOST", "localhost");
DEFINE("DBUSER", "root");
DEFINE("DBPASS", "");
DEFINE("DBNAME", "radius");
*/

/************************************* PROD ENV */
DEFINE("DEBUG", false);
DEFINE("DBHOST", "localhost");
DEFINE("DBUSER", "radius");
DEFINE("DBPASS", "senai127");
DEFINE("DBNAME", "radius");

// When set to true, all successful user logins are written to database
// When set to false, only the last successful user login is written to database
$UPDATE = false;

//// Hotel identification

$identificator = "HOTEL_ID";			// Hotel identifcator string logged to database

//// Information to get
//Be aware that RADIUS username is generated from email and room number and password is generated from familyname and surname, so don't disable all of them at once.

$askForRoomNumber = false;
$askForEmailAddress = true;
$askForFamilyName = true;
$askForTermsOfUse = true;
$askForCourse = true;
//$confirmationCode = "0000";

//// Language function

$validLanguages = Array('en');	// When adding languages, add a new entry here
$language = "en";				// May be superseeded by passing language parameter in URL

//TODO: function t approach of assigning all strings is not very effective (all strings assigned on every run!)
function t($string) {

global $language;

if ($language == "en")
{
// UI language strings
$macAdressErrorMessage_string = "Your device doesn't provide all necessary data for connection.";
$databaseConnectErrorMessage_string = "Cannot connect to the database. ";
$databaseRegisterErrorMessage_string = "Cannot register your user account.";
$databaseCheckErrorMessage_string = "Cannot check database for user.";
$incorrectInput_string = "The input you provided is incorrect.";
$incorrectConfirmationCode_string = "The code is incorrect.";
// $noScript_string = "Please click on Continue if your browser doesn't support JavaScript.";
}
  
// Conf build

return $$string;

}

?>
