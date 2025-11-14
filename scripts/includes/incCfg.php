<?php  
// === Error Reporting === \\
error_reporting(E_ALL);
ini_set('display_errors', 1);

// === Includes === \\
require_once("clsTemplate.php");
require_once("clsMail.php");
require_once("clsMaria.php");
require_once("clsOra.php");
require_once("incHelperFunctions.php");
require_once("clsUser.php");
require_once("clsSession.php");
require_once("incStaticFiles.php");
require_once("objects/intRoutable.php");
require_once("clsLevel.php");
require_once("incCredentials.php");

require_once(__DIR__ . "/../Header.php");

// Composer
require (__DIR__ . "/../../vendor/autoload.php");

// === Constants === \\

// Misc
define("REQUESTER_IP", $_SERVER['REMOTE_ADDR']);
define("URL_HTBASE", "http://" . $_SERVER['HTTP_HOST'] . "/");
define("ROUTE", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
define("MODE", $_GET['mode'] ?? '');
define("DEBUG_MAIL", "janschilfahrt@gmail.com");
define("DIR_ROOT", $_SERVER['DOCUMENT_ROOT']);
define("DIR_ASSETS", DIR_ROOT . "/resources");
define("DIR_SCRIPTS", DIR_ROOT . "/scripts");
define("DIR_JS", DIR_ROOT . "/javascript");
define("INS", $_REQUEST);

// Mail
define("SMTP_HOST", "smtp.gmail.com");
define("SMTP_PORT", 587);
define("SMTP_USER", "janschilfahrt@gmail.com");
define("SMTP_SECURE", "tls");

// === Auth === \\
$session = new Session();
$session->load_session_data();

$user = new User(SESS_USERID);
define("ARR_USERINFO", $user->load_user());

// === Misc Function calls === \\
if(substr(ROUTE, 1, 3) != 'api' && !str_contains(ROUTE, "sitemap")) {
    load_essential_scripts();
}