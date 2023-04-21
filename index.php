<?php

/*********************************************************************/
/* Workflow:                                                         */
/*                                                                   */
/* SignUp() submit --> Create / Update RADIUS user --> Login() */
/*********************************************************************/

// global is used because pfSense php interpreter doesn't take variable definitions in functions
global $user, $password;

global $ra, $userName;
global $zone, $redirurl;

global $UPDATE;

// Config file
include "captiveportal-config.php";

// Users data file
include "captiveportal-users.php";

// Get IP and mac address
$ipAddress = $_SERVER['REMOTE_ADDR'];
#run the external command, break output into lines
$arp = `arp $ipAddress`;
$lines = explode(" ", $arp);

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
		SignUp();
	else
		SignUp();
	$db->close();
	die();
}

if (isset($_GET['zone']))
	$zone = cleanInput($_GET["zone"]);

if (isset($_GET['redirurl']))
	$redirurl = cleanInput($_GET["redirurl"]);

if (isset($_POST["ra"]))
	$ra = cleanInput($_POST["ra"]);
else
	$ra = false;

if (isset($_POST["userName"]))
	$userName = cleanInput($_POST["userName"]);
else
	$userName = false;

if (isset($_POST["course"]))
	$course = cleanInput($_POST["course"]);
else
	$course = false;

if (isset($_POST["termsOfUse"]) && isset($_POST["connect"])) {
	$interval = new DateInterval('P0Y1M');

	$registrationDate = date("Y-m-d");

	if (strtoupper($course) == "RDS") {
		$interval = new DateInterval('P1Y2M');
	} else if (strtoupper($course) == "DS") {
		$interval = new DateInterval('P3Y7M');
	}

	$expirationDate = new DateTime();
	$expirationDate = $expirationDate->add($interval)->format('Y-m-d');

	//Store user data in text file
	gravar($ra, $userName, $course, $expirationDate);

	$db = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
	if (mysqli_connect_errno()) {
		if (DEBUG == true)
			$error_message = showErrorText('databaseConnectErrorMessage_string') . utf8_encode(mysqli_connect_errno());
		else
			$error_message = showErrorText('databaseConnectErrorMessage_string');
		SignUp();
	} else {
		if ($macAddress != NULL) {
			$columnNames = "";
			$valueNames = "";
			$updateQuery = "";
			$create = false;

			$parameters = array();
			$parameters['ra'] = $ra;
			$parameters['userName'] = strtoupper($userName);
			$parameters['macAddress'] = $macAddress;
			$parameters['ipAddress'] = $ipAddress;
			$parameters['course'] = strtoupper($course);
			$parameters['registrationDate'] = $registrationDate;
			$parameters['expirationDate'] = $expirationDate;


			if ($UPDATE == true) {
				if (!$statement = $db->prepare("SELECT * FROM reg_users WHERE macAddress = ? AND ra = ? LIMIT 1"))
					$dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (1) :");
				else {
					$statement->bind_param('ss', $macAddress, $ra);
					if (!$statement->execute())
						dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (1) :");
					$statement->store_result();
					if ($statement->num_rows != 0) {
						$statement->close();
						if (!$statement = $db->prepare("UPDATE reg_users SET ra = ?, userName = ?, macAddress = ?, ipAddress = ?, course = ?, registrationDate = ?, expirationDate = ? WHERE macAddress = ? AND ra = ?"))
							dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (1) :");
						else {
							$statement->bind_param("sssssssss", $parameters['ra'], $parameters['userName'], $parameters['macAddress'], $parameters['ipAddress'], $parameters['course'], $parameters['registrationDate'], $parameters['expirationDate'], $parameters['macAddress'], $parameters['ra']);
							if (!$statement->execute())
								dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (1) :");
							$statement->close();
						}
					} else {
						$statement->close();
						$create = true;
					}
				}
			} else
				$create = true;

			if ($create == true) {
				if (!$statement = $db->prepare("INSERT INTO reg_users (ra, userName, macAddress, ipAddress, course, registrationDate, expirationDate) VALUES (?, ?, ?, ?, ?, ?, ?)"))
					dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (1) :");
				else {
					$statement->bind_param("sssssss", $parameters['ra'], $parameters['userName'], $parameters['macAddress'], $parameters['ipAddress'], $parameters['course'], $parameters['registrationDate'], $parameters['expirationDate']);
					if (!$statement->execute())
						dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (1) :");
					$statement->close();
				}
			}

			// User name and password for RADIUS
			$user = $ra;
			$password = $userName;

			if (!$statement = $db->prepare("SELECT username FROM radcheck WHERE username = ?"))
				dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (2) :");
			else {
				$statement->bind_param("s", $user);
				if (!$statement->execute())
					dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (2) :");

				$statement->store_result();
				if ($statement->num_rows != 0) {
					$statement->close();
					if (!$statement = $db->prepare("UPDATE radcheck SET value = ? WHERE username = ?"))
						dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (2) :");
					else {
						$statement->bind_param("ss", $password, $user);
						if (!$statement->execute())
							dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (2) :");
					}
				} else {
					$statement->close();
					if (!$statement = $db->prepare("INSERT INTO radcheck (username, attribute, op, value) VALUES (?, 'Cleartext-Password', ':=', ?)"))
						dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (2) :");
					else {
						$statement->bind_param("ss", $user, $password);
						if (!$statement->execute())
							dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (2) :");
					}
				}
			}

			$statement->close();
			if (!$statement = $db->prepare("SELECT username FROM radusergroup WHERE username = ?"))
				dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (3)a :");
			else {
				$statement->bind_param("s", $user);
				if (!$statement->execute())
					dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (3) :");
				else {
					$statement->store_result();
					if ($statement->num_rows == 0) {
						$statement->close();
						if (!$statement = $db->prepare("INSERT INTO radusergroup (username, groupname) VALUES (?, 'Free')"))
							dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (3) :");
						else {
							$statement->bind_param("s", $user);
							if (!$statement->execute())
								dbError($db, showErrorText('databaseRegisterErrorMessage_string') . " (3) :");
							$statement->close();
						}
					}
				}
			}
			$db->close();
			Login();
		} else
			SignUp();
	}
} else
	SignUp();

function Login()
{
	global $user;
	global $password;
?>
	<!DOCTYPE html>
	<html>
	<!-- Do not modify anything in this form as pfSense needs it exactly that way -->

	<body>
		<?php include "captiveportal-successful.html"; ?>
		<form id="auto-login" name="loginForm" method="post" action="$PORTAL_ACTION$">
			<input name="auth_user" type="hidden" value="<?php echo $user; ?>">
			<input name="auth_pass" type="hidden" value="<?php echo $password; ?>">
			<input name="zone" type="hidden" value="$PORTAL_ZONE$">
			<input name="redirurl" type="hidden" value="https://www.sp.senai.br/">
			<input id="submitbtn" name="accept" type="submit" value="Continue">
		</form>
		<script type="text/javascript">
			document.getElementById("submitbtn").click();
		</script>
	</body>

	</html>
<?php
}

function SignUp()
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
		<script src="./captiveportal-app.js" defer></script>
		<title>AAPM - Portal</title>
	</head>

	<body>
		<header>
			<img src="./captiveportal-aapm-logo.png" alt="AAPM Logo" class="logo" />
		</header>
		<main>
			<h1 class="login__title">Login</h1>
			<form id="login" class="login" method="post" action="$PORTAL_ACTION$">
				<div class="user-container">
					<label for="user" class="user__name">Usuário</label>
					<input name="auth_user2" type="text" class="user__input" placeholder="Usuário" />
					<span class="user__error">Preencha o usuário</span>
				</div>
				<div class="password-container">
					<label for="password" class="password__name">Senha</label>
					<input name="auth_pass2" type="password" class="password__input" placeholder="Senha" />
					<span class="password__error">Preencha a senha</span>
				</div>
				<input name="zone" type="hidden" value="$PORTAL_ZONE$">
				<!-- <input name="redirurl" type="hidden" value="https://www.sp.senai.br/"> -->
				<input class="login__button" name="accept" type="submit" value="Login">
				<span class="login__not-registred">Não possui cadastro?</span>
			</form>
			<h1 class="signUp__title">Cadastro</h1>
			<form id="enregistrement" method='post' action="?<?php if (isset($zone))
																	echo "zone=$zone"; ?>" class="register">
				<fieldset>
					<div class="ra-container">
						<label for="ra" class="ra__name">R.A (Número de Matrícula)</label>
						<input type="number" class="ra__input" placeholder="R.A" id="ra" name="ra" value="<?php echo $ra; ?>" />
						<span class="ra__error">Preencha o R.A</span>
						<span class="ra__error-contribuitor">Este R.A não é contribuinte!</span>
					</div>
					<div class="full-name-container">
						<label for="userName" class="full-name__name">Nome completo</label>
						<input type="text" class="full-name__input" placeholder="Nome completo" id="userName" name="userName" value="<?php echo $userName; ?>" />
						<span class="full-name__error">Preencha o nome completo!</span>
					</div>
					<div class="select-container">
						<label for="" class="course__name">Curso</label>
						<select class="courses-container" name="course">
							<option class="course__default" value="">
								Selecione o seu curso
							</option>
							<option class="course" value="<?php echo $course = "rds"; ?>">
								Redes de computadores
							</option>
							<option class="course" value="<?php echo $course = "ds"; ?>">
								Análise e desenvolvimento de sistemas
							</option>
						</select>
						<span class="course__error">Selecione um curso!</span>
					</div>
					<div class="terms-container">
						<div class="checkbox-container">
							<input type="checkbox" class="terms__checkbox" name="termsOfUse" id="termsOfUse" value="termsOfUSe" />
							<label class="terms__text" for="termsOfUse">Li e aceito os
								<span class="terms__link">termos de uso e condições</span>
							</label>
						</div>
						<span class="checkbox__error">Aceite os termos de uso!</span>
					</div>
					<div class="checkbox__terms">
						<h2 class="terms__title">Termos de uso</h2>
						<p class="terms__text-conditions">
							<b>Termos de uso da rede sem fio AAPM</b>
						</p>
						<p class="terms__text-conditions">
							Este termo possui informações importantes sobre seus direitos e obrigações. 
							Ao clicar em “Declaro que li e concordo com os Termos e Condições 
							de Uso” você declara que concordou com este termo e com a Política 
							de Privacidade e que está ciente do tratamento dos seus dados 
							pessoais como informado nos referidos documentos.
						</p>
						<p class="terms__text-conditions">
							O acesso à rede sem fio é permitido somente aos visitantes 
							devidamente cadastrados. A senha de acesso obtida por meio do 
							cadastro é pessoal e intransferível, sendo o visitante o único 
							responsável por qualquer ato (legal ou ilegal) decorrente do uso 
							da rede a partir de seu usuário e senha.
						</p>
						<p class="terms__text-conditions">
							<b>São proibidas as seguintes condutas por parte do USUÁRIO:</b>
						</p>
						<p class="terms__text-conditions">
							a) Mostrar, armazenar ou transmitir textos, imagens ou sons que possam ser considerados ofensivos ou abusivos.
							</br>
							</br>
							b) Instigar, ameaçar, ofender, abalar a imagem, invadir a privacidade ou prejudicar outros usuários da internet.
							</br>
							</br>
							e) Violar ou tentar violar sistemas de segurança, quebrando ou tentando adivinhar a identidade eletrônica de outro usuário, senhas ou outros dispositivos de segurança.
							</br>
							</br>
							g) Causar ou tentar causar a indisponibilidade dos serviços e/ou destruição de dados ou engajar-se em ações que possam ser consideradas como violação da segurança computacional.
							</br>
							</br>
							h) Criar falsa identidade ou assumir, sem autorização, a identidade de outro usuário; utilizar-se da Internet e outros serviços disponibilizados com o intuito de cometer fraude; invadir a privacidade de terceiros, buscando acesso a senhas e dados privativos, violando sistemas de segurança de informação ou redes privadas de computador conectadas à Internet.
							</br>
							</br>
							i) Responder pelo mau uso dos recursos computacionais em qualquer circunstância.
						</p>
						<p class="terms__text-conditions">
							<b>Políticas de privacidade</b>
						</p>
						<p class="terms__text-conditions">
							Quando o usuário acessar os serviços da rede Wi-Fi AAPM, algumas informações 
							(Dados Pessoais) sobre o usuário poderão ser coletadas, guardadas e 
							tratadas pela AAPM. Entende-se por "Dados Pessoais" qualquer 
							informação que possa identificá-lo diretamente - como o seu nome - 
							ou indiretamente - como o número do seu endereço IP 
							<i>(Internet Protocol).</i>
						</p>
						<p class="terms__text-conditions">
							Os dados pessoais poderão ser coletados de maneira direta 
							(quando preenchidos por você) ou de maneira indireta 
							(com dados da execução do próprio acesso). Os dados diretos serão 
							coletados por meio de um “formulário de cadastro” que será 
							obrigatório para acesso ao serviço. Eventualmente, algum dado 
							poderá ser fornecido por um parceiro ou fornecedor, quando 
							necessário para a prestação de serviço do WiFi.
						</p>
						<div class="terms-confirmation">
							<button class="terms__button">OK</button>
						</div>
					</div>
					<input type="button" class="register__button" name="connecter" value="Cadastre-se">
					<input type="hidden" name="connect" value="true">
				</fieldset>
				<span class="register__already-registred">Já é registrado?</span>
				</fieldset>
			</form>
			<span class="another-option">OU</span>
			<form method="post" action="$PORTAL_ACTION$" class="voucher">
				<div class="voucher-container">
					<label for="" class="voucher__name">Voucher</label>
					<input name="auth_voucher" type="text" class="voucher__input" placeholder="Código" />
					<span class="voucher__error">Preencha o código do voucher!</span>
					<!-- <input name="redirurl" type="hidden" value="https://www.sp.senai.br/" /> -->
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