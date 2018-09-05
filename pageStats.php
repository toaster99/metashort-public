<?php
//
// Todo
//

// 
// Includes
//
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/environment.php';

//
// Permissions check
//
if (!$params['loggedIn']) {
	header('Location: /pro?needPro=y');
}

//
// Services
//
$serviceSites = new ServiceSites($dbConnection);
$serviceShortenedURLs = new ServiceShortenedURLs($dbConnection);
$serviceMeta = new ServiceMeta($dbConnection);
$serviceClickTracking = new serviceClickTracking($dbConnection);

$output = array();

// Grab the short url
if (!isset($_REQUEST['shortURL']) || trim($_REQUEST['shortURL']) == '') {
	header("Location: /index.php");
	exit();
}
$params['shortURL'] = $_REQUEST['shortURL'];

// Fetch the shortenedURL object 
$shortenedURL = $serviceShortenedURLs->get(array(
	'shortURL' => $params['shortURL']
));

// Check that we found the object
if (!isset($shortenedURL) || sizeof($shortenedURL) <= 0) {
	header("Location: /index.php");
	exit();	
}
$shortenedURL = $shortenedURL[0];

// Now get the site object 
$site = $serviceSites->get(array(
	'ID' => $shortenedURL['siteID']
));
// Check that we got the site
if (!isset($site) || sizeof($site) <= 0) {
	header("Location: /index.php");
	exit();	
}
$site = $site[0];
$originalURL = $site['URL'];

if ($params['userID'] != $site['userID']) {
	header('Location: /account');
}

//
// Populate data
//
$clicksData = $serviceClickTracking->visitSummary($site['ID'], 30);
$params['data30DayClicks'] = json_encode(array_values($clicksData));
$params['labels30DayClicks'] = json_encode(array_keys($clicksData));

$referralData = $serviceClickTracking->referralSummary($site['ID'], 30, 5);
$params['dataReferralSources'] = json_encode(array_column($referralData, 'times'));
$params['labelsReferralSources'] = json_encode(array_column($referralData, 'referer'));

//
// Output HTML
//
$output[] = $twig->render('pageStats.twig', $params);
echo implode($output);