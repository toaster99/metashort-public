<?php
//
// Todo
//	[] Create expiration on editing for non-pro members
//

// 
// Includes
//
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/environment.php';


//
// Services
//
$serviceSites = new ServiceSites($dbConnection);
$serviceShortenedURLs = new ServiceShortenedURLs($dbConnection);
$serviceMeta = new ServiceMeta($dbConnection);

$output = array();

//
// Show needs pro message
//
if (isset($_REQUEST['needPro'])) {
	$params['errorMessages'][] = array(
		'role' => 'warning',
		'text' => 'This feature requires a MetaShort Pro Subscription!'
	);
}

//
// Output HTML
//
$output[] = $twig->render('pageProPromo.twig', $params);
echo implode($output);