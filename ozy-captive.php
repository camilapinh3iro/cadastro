﻿<?php

define("APP_BUILD", "OZY's CAPTIVE PORTAL FOR RADIUS/MySQL authentication v0.49 2018093001");
/*********************************************************************/
/* Workflow:                                                         */
/*                                                                   */
/* WelcomePage() --submit--> Create / Update RADIUS user --> Login() */
/*********************************************************************/

// global is used because pfSense php interpreter doesn't take variable definitions in functions
global $identificator;
global $userName, $password;

global $ra, $userName;
global $zone, $redirurl;

global $askForRoomNumber, $askForEmailAddress, $askForFamilyName, $askForSurName, $askForNewsletter, $askForTermsOfUse;

global $UPDATE;

// Config file
include "captiveportal-config.php";

// Get IP and mac address
$ipAddress = $_SERVER['REMOTE_ADDR'];
#run the external command, break output into lines
$arp = `arp $ipAddress`;
$lines = explode(" ", $arp);
$badCheck = false;

if (!empty($lines[3]))
	$macAddress = $lines[3]; // Works on FreeBSD
else
	$macAddress = "fa:ke:ma:c:ad:dr"; // Fake MAC on dev station which is probably not FreeBSD

// Clean input function
function cleanInput($input)
{
	$search = array(
		'@<script[^>]*?>.*?</script>@si',
		/* strip out javascript */
		'@<[\/\!]*?[^<>]*?>@si',
		/* strip out HTML tags */
		'@<style[^>]*?>.*?</style>@siU',
		/* strip style tags properly */
		'@<![\s\S]*?--[ \t\n\r]*>@' /* strip multi-line comments */
	);

	$output = preg_replace($search, '', $input);
	return $output;
}
function dbError($db, $errMessage)
{
	trigger_error($errMessage . utf8_encode($db->error));

	if (DEBUG == true)
		WelcomePage();
	else
		WelcomePage();
	$db->close();
	die();
}

// pfSense 2.3 fix, see https://forum.pfsense.org/index.php?topic=105567.0
if (isset($_GET['zone']))
	$zone = cleanInput($_GET["zone"]);

if (isset($_GET['redirurl']))
	$redirurl = cleanInput($_GET["redirurl"]);

if (isset($_POST["familyName"]))
	$userName = cleanInput($_POST["familyName"]);
else
	$userName = false;
if ((strlen($userName) < 2) && ($askForFamilyName == true)) {
	$checkMessage = t('incorrectInput_string');
	$badCheck = true;
}

if (isset($_POST["emailAddress"]))
	$ra = cleanInput($_POST["emailAddress"]);
else
	$ra = false;
if ((!filter_var($ra, FILTER_VALIDATE_EMAIL)) && ($askForEmailAddress == true)) {
	$checkMessage = t('incorrectInput_string');
	$badCheck = true;
}

if (((isset($_POST["termsOfUse"])) || ($askForTermsOfUse == false)) && isset($_POST["connect"])) {
	$regDate = date("Y-m-d H:i:s");

	//tentar deletar isso
	if (isset($_POST["newsletter"]))
		$newsletter = 1;
	else
		$newsletter = 0;

	if ($badCheck == true) {
		WelcomePage();
		die();
	}

	$db = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
	if (mysqli_connect_errno()) {
		if (DEBUG == true)
			$error_message = t('databaseConnectErrorMessage_string') . utf8_encode(mysqli_connect_errno());
		else
			$error_message = t('databaseConnectErrorMessage_string');
		WelcomePage();
	} else {
		if ($macAddress != NULL) {
			$columnNames = "";
			$valueNames = "";
			$updateQuery = "";
			$create = false;

			// Don't want to write long prepared statements, have php write them for me

			$parameters = array();
			$parameters['familyName'] = $userName;
			$parameters['surName'] = '$surName';
			$parameters['roomNumber'] = '$roomNumber';
			$parameters['emailAddress'] = $ra;
			$parameters['macAddress'] = $macAddress;
			$parameters['ipAddress'] = $ipAddress;
			$parameters['regDate'] = $regDate;
			$parameters['identificator'] = $identificator;
			$parameters['newsletter'] = 0;

			if ($UPDATE == true) {
				if (!$statement = $db->prepare("SELECT * FROM reg_users WHERE macAddress = ? AND emailAddress = ? LIMIT 1"))
					$dbError($db, t('databaseRegisterErrorMessage_string') . " (1) :");
				else {
					$statement->bind_param('ss', $macAddress, $ra);
					if (!$statement->execute())
						dbError($db, t('databaseRegisterErrorMessage_string') . " (1) :");
					$statement->store_result();
					if ($statement->num_rows != 0) {
						$statement->close();
						if (!$statement = $db->prepare("UPDATE reg_users SET familyName = ?, surName = ?, roomNumber = ?, emailAddress = ?, macAddress = ?, ipAddress = ?, regDate = ?, identificator = ?, newsletter = ? WHERE macAddress = ? AND emailAddress = ?"))
							dbError($db, t('databaseRegisterErrorMessage_string') . " (1) :");
						else {
							$statement->bind_param("sssssssssss", $parameters['familyName'], $parameters['surName'], $parameters['roomNumber'], $parameters['emailAddress'], $parameters['macAddress'], $parameters['ipAddress'], $parameters['regDate'], $parameters['identificator'], $parameters['newsletter'], $parameters['macAddress'], $parameters['emailAddress']);
							if (!$statement->execute())
								dbError($db, t('databaseRegisterErrorMessage_string') . " (1) :");
							$statement->close();
						}
					} else {
						$statement->close();
						$create = true;
					}
				}
			} else
				$create = true;

			// I know this is dirty, but I don't feel like recoding everything into subfunctions
			if ($create == true) {
				if (!$statement = $db->prepare("INSERT INTO reg_users (familyName, surName, roomNumber, emailAddress, macAddress, ipAddress, regDate, identificator, newsletter) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"))
					dbErrror($db, t('databaseRegisterErrorMessage_string') . " (1) :");
				else {
					$statement->bind_param("sssssssss", $parameters['familyName'], $parameters['surName'], $parameters['roomNumber'], $parameters['emailAddress'], $parameters['macAddress'], $parameters['ipAddress'], $parameters['regDate'], $parameters['identificator'], $parameters['newsletter']);
					if (!$statement->execute())
						dbError($db, t('databaseRegisterErrorMessage_string') . " (1) :");
					$statement->close();
				}
			}

			// User name and password for RADIUS
			$userName = $ra;
			$password = $userName;

			if (!$statement = $db->prepare("SELECT username FROM radcheck WHERE username = ?"))
				dbError($db, t('databaseRegisterErrorMessage_string') . " (2) :");
			else {
				$statement->bind_param("s", $userName);
				if (!$statement->execute())
					dbError($db, t('databaseRegisterErrorMessage_string') . " (2) :");

				$statement->store_result();
				if ($statement->num_rows != 0) {
					$statement->close();
					if (!$statement = $db->prepare("UPDATE radcheck SET value = ? WHERE username = ?"))
						dbError($db, t('databaseRegisterErrorMessage_string') . " (2) :");
					else {
						$statement->bind_param("ss", $password, $userName);
						if (!$statement->execute())
							dbError($db, t('databaseRegisterErrorMessage_string') . " (2) :");
					}
				} else {
					$statement->close();
					if (!$statement = $db->prepare("INSERT INTO radcheck (username, attribute, op, value) VALUES (?, 'Cleartext-Password', ':=', ?)"))
						dbError($db, t('databaseRegisterErrorMessage_string') . " (2) :");
					else {
						$statement->bind_param("ss", $userName, $password);
						if (!$statement->execute())
							dbError($db, t('databaseRegisterErrorMessage_string') . " (2) :");
					}
				}
			}

			$statement->close();
			if (!$statement = $db->prepare("SELECT username FROM radusergroup WHERE username = ?"))
				dbError($db, t('databaseRegisterErrorMessage_string') . " (3)a :");
			else {
				$statement->bind_param("s", $userName);
				if (!$statement->execute())
					dbError($db, t('databaseRegisterErrorMessage_string') . " (3) :");
				else {
					$statement->store_result();
					if ($statement->num_rows == 0) {
						$statement->close();
						if (!$statement = $db->prepare("INSERT INTO radusergroup (username, groupname) VALUES (?, 'Free')"))
							dbError($db, t('databaseRegisterErrorMessage_string') . " (3) :");
						else {
							$statement->bind_param("s", $userName);
							if (!$statement->execute())
								dbError($db, t('databaseRegisterErrorMessage_string') . " (3) :");
							$statement->close();
						}
					}
				}
			}
			$db->close();
			Login();
		} else
			WelcomePage();
	}
} else
	WelcomePage();

function Login()
{
	global $userName;
	global $password;
	?>
	<!DOCTYPE html>
	<html>
	<!-- Do not modify anything in this form as pfSense needs it exactly that way -->

	<body>
		<?php print t('noScript_string'); ?>
		<form name="loginForm" method="post" action="$PORTAL_ACTION$">
			<input name="auth_user" type="hidden" value="<?php echo $userName; ?>">
			<input name="auth_pass" type="hidden" value="<?php echo $password; ?>">
			<input name="zone" type="hidden" value="$PORTAL_ZONE$">
			<input name="redirurl" type="hidden" value="https://www.google.com/">
			<input id="submitbtn" name="accept" type="submit" value="Continue">
		</form>
		<script type="text/javascript">
			document.getElementById("submitbtn").click();
		</script>
	</body>

	</html>
	<?php
}

function WelcomePage()
{
	global $ra, $userName;
	global $zone, $redirurl;
	?>

	<!DOCTYPE html>
	<html lang="pt-BR">

	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="description" content="Website AAPM." />
		<link rel="shortcut icon" href="./captiveportal-senai-icon.jfif" type="image/x-icon" />
		<link rel="stylesheet" href="./captiveportal-style.css" />
		<!-- <link rel="preconnect" href="https://fonts.googleapis.com" />
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
		<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600&display=swap" rel="stylesheet" /> -->
		<script src="./captiveportal-app.js" defer></script>
		<title>AAPM - Portal</title>
	</head>
	<body>
		<header>
			<img src="./captiveportal-aapm-logo.png" alt="AAPM Logo" class="logo" />
		</header>
		<main>
			<h1 class="title">Cadastro</h1>
			<form id="enregistrement" method='post' action="?<?php if (isset($zone))
				echo "zone=$zone"; ?>" class="register">
				<fieldset>
					<div class="ra-container">
						<label for="" class="ra__name">R.A</label>
						<input type="email" class="ra__input" placeholder="R.A" id="emailAddress" name="emailAddress"
							value="<?php echo $ra; ?>" />
						<span class="ra__error">Preencha o R.A</span>
					</div>
					<div class="full-name-container">
						<label for="" class="full-name__name">Nome completo</label>
						<input type="text" class="full-name__input" placeholder="Nome completo" id="familyName"
							name="familyName" value="<?php echo $userName; ?>" />
						<span class="full-name__error">Preencha o nome completo!</span>
					</div>
					<div class="select-container">
						<label for="" class="course__name">Curso</label>
						<select class="course-container" name="course">
							<option class="course__default" value="">
								Selecione o seu curso
							</option>
							<option class="course__network" value="redes">
								Redes de computadores
							</option>
							<option class="course__system-development" value="ds">
								Análise e desenvolvimento de sistemas
							</option>
						</select>
						<span class="course__error">Selecione um curso!</span>
					</div>
					<div class="terms-container">
						<div class="checkbox-container">
							<input type="checkbox" class="terms__checkbox" name="termsOfUse" id="termsOfUse"
								value="termsOfUSe" />
							<label class="terms__text" for="termsOfUse">Li e aceito os
								<span class="terms__link">termos de uso e condições</span>
							</label>
						</div>
						<span class="checkbox__error">Aceite os termos de uso!</span>
					</div>
					<div class="checkbox__terms">
						<h2 class="terms__title">Termos de uso</h2>
						<p class="terms__text-conditions">
							Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolores
							ducimus deserunt a vitae id sit vero ex, dicta dolor adipisci et!
							Incidunt eius veniam perspiciatis veritatis? Cupiditate earum
							pariatur alias laudantium possimus amet assumenda, eveniet nemo
							laborum ipsa odio! Praesentium neque placeat sequi repellat
							possimus, labore eveniet aspernatur voluptatum dolor fugit nihil
							nostrum accusamus, tenetur animi repudiandae expedita. Eaque,
							quisquam!
						</p>
						<p class="terms__text-conditions">
							Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolores
							ducimus deserunt a vitae id sit vero ex, dicta dolor adipisci et!
							Incidunt eius veniam perspiciatis veritatis? Cupiditate earum
							pariatur alias laudantium possimus amet assumenda, eveniet nemo
							laborum ipsa odio! Praesentium neque placeat sequi repellat
							possimus, labore eveniet aspernatur voluptatum dolor fugit nihil
							nostrum accusamus, tenetur animi repudiandae expedita. Eaque,
							quisquam!
						</p>
						<div class="terms-confirmation">
							<button class="terms__button">OK</button>
						</div>
					</div>
					<input type="submit" class="register__button" name="connecter" value="Cadastre-se">
					<input type="hidden" name="connect" value="true">
				</fieldset>
				<a href="#" class="register__already-registred">Já é registrado?</a>
				</fieldset>
			</form>
			<span class="another-option">OU</span>
			<form method="post" action="$PORTAL_ACTION$" class="voucher">
				<div class="voucher-container">
					<label for="" class="voucher__name">Voucher</label>
					<input name="auth_voucher" type="text" class="voucher__input" placeholder="Código" />
					<span class="voucher__error">Preencha o código do voucher!</span>
					<input name="redirurl" type="hidden" value="https://www.sp.senai.br/" />
					<input name="zone" type="hidden" value="$PORTAL_ZONE$" />
				</div>
				<input class="voucher__button" name="accept" type="submit" value="Entrar temporariamente" />
			</form>
		</main>
		<footer>
			<p class="footer__text">
				Copyright ©️ | Todos os Direitos Reservados | SENAI
			</p>
		</footer>
	</body>

	</html>
	<?php
}
?>