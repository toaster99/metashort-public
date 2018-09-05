<?php
// 
// Includes
//
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/environment.php';

//
// Headers
//
header("HTTP/1.1 200 OK");
header("Content-Type: text/html");

//
// Permissions check
//
if (!$params['loggedIn']) {
	header('Location: /pro?needPro=y');
}

//
// Services
//
$output = array();
$serviceSites = new ServiceSites($dbConnection);
$serviceShortenedURLs = new ServiceShortenedURLs($dbConnection);
$serviceMeta = new ServiceMeta($dbConnection);
$serviceClickTracking = new serviceClickTracking($dbConnection);
//
// Handle page actions
//
if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {
		case 'deleteSite':
			$siteID = $_REQUEST['siteID'];
			if ($siteID > 0) {
				// Get the marked for deletion site
				$markedSite = $serviceSites->get(array(
					'ID' => $siteID,
					'userID' => $params['userID']
				));
			}
			// Validate the site to delete
			if ($siteID <= 0 || !isset($markedSite) || !$markedSite || sizeof($markedSite) <= 0) {
				$params['errorMessages'][] = array(
					'role' => 'error',
					'text' => 'There was an issue deleting this site, please try again!'
				);
				break;
			}
			$markedSite = $markedSite[0];
			// Delete the site
			$deleteResult = $serviceSites->delete($markedSite);
			if ($deleteResult) {
				// Delete related shortened URL (should only be one)
				$markedShortenedURLs = $serviceShortenedURLs->get(array(
					'siteID' => $markedSite['ID']
				));
				foreach ($markedShortenedURLs as $shortenedURLKey => $shortenedURLValue) {
					$serviceShortenedURLs->delete($shortenedURLValue);
				}
				// Delete related meta tags
				$markedMetas = $serviceMeta->get(array(
					'siteID' => $markedSite['ID']
				));
				foreach ($markedMetas as $markedMetaKey => $markedMetaValue) {
					$serviceMeta->delete($markedMetaValue);
				}
				// Tell user everything is good
				$params['errorMessages'][] = array(
					'role' => 'success',
					'text' => 'MetaShort link was deleted 👍'
				);
			} else {
				$params['errorMessages'][] = array(
					'role' => 'error',
					'text' => 'There was an issue deleting this site, please try again!'
				);
				break;
			}
			break;
		default:
			break;
	}
}

//
// Set data params
//
$userSites = $serviceSites->get(array(
	'userID' => $params['userID']
));
if (isset($userSites) && $userSites && sizeof($userSites) > 0) {
	foreach ($userSites as $siteKey => $siteValue) {
		// Get the short URL
		$siteShortened = $serviceShortenedURLs->get(array(
			'siteID' => $siteValue['ID']
		));
		if (isset($siteShortened) && sizeof($siteShortened) > 0) {
			$userSites[$siteKey]['shortURL'] = $siteShortened[0]['shortURL'];
		}
		// Now get the past 30 day stats
		$userSites[$siteKey]['visitStats'] = $serviceClickTracking->visitSummary($siteValue['ID'], 30);
	}
}

$params['userSites'] = $userSites;
// First login message
if(isset($_REQUEST['firstLogin'])) {
	$params['errorMessages'][] = array(
		'role' => 'success',
		'text' => 'Welcome to MetaShort Pro and thank you for being an awesome customer!'
	);
}
//
// Output HTML
//
$output[] = $twig->render('account/index.twig', $params);
echo implode($output);