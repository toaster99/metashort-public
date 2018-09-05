<?php
error_reporting(E_ERROR);
include_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
// Figure out if this is dev or prod
if (in_array($_SERVER['HTTP_HOST'], array('alexs-macbook-pro-2.local:5757','metashort.dev'))) {
	include_once $_SERVER['DOCUMENT_ROOT'] . '/config/devConfig.php';
} else {
	include_once $_SERVER['DOCUMENT_ROOT'] . '/config/prodConfig.php';
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/services/serviceSites.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/services/serviceShortenedURLs.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/services/serviceMeta.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/services/serviceUsers.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/services/serviceClickTracking.php';

//
// Force https
//
if (!in_array($_SERVER['HTTP_HOST'], array('alexs-macbook-pro-2.local:5757','metashort.dev'))) {
	if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
		if(!headers_sent()) {
			header("Status: 301 Moved Permanently");
			header(sprintf(
				'Location: https://%s%s',
				$_SERVER['HTTP_HOST'],
				$_SERVER['REQUEST_URI']
			));
			exit();
		}
	}
}

//
// Twig config
//
$twigLoader = new Twig_Loader_Filesystem($_SERVER['DOCUMENT_ROOT'] . '/views');
$twig = new Twig_Environment($twigLoader, array(
    'cache' => false,
));

//
// Stripe setup
//
\Stripe\Stripe::setApiKey($siteConfig['stripe']['secret_key']);

//
// Session start
//
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

//
// Setup params
//
$params = array(
	'isDevServer' => in_array($_SERVER['HTTP_HOST'], array('metashort.dev')),
	'errorMessages' => array(),
	'loggedIn' => isset($_SESSION['userID']) && trim($_SESSION['userID']) != '',
	'monthlySubscriptionCost' => 10,
	'path' => $_SERVER['PHP_SELF']
);

if ($params['loggedIn']) {
	$params['userID'] = $_SESSION['userID'];
	$params['emailAddress'] = $_SESSION['emailAddress'];
} else {
	$params['userID'] = 0;
}

//
// Restricted paths that can't be used to generate a short link
//
$restrictedLinkPaths = array(
	'about',
	'faqs',
	'login',
	'signup',
	'index'
);

//
// Database connection config
//
$dbHost = $siteConfig['database']['host'];
$dbDatabase = $siteConfig['database']['database'];
$dbUser = $siteConfig['database']['user'];
$dbPassword = $siteConfig['database']['password'];

$dbConnection = new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbDatabase, $dbUser, $dbPassword);