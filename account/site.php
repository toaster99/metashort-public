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
	echo 'Unauthorized';
	die();
}

//
// Services
//
$output = array();
$serviceSites = new ServiceSites($dbConnection);
$serviceShortenedURLs = new ServiceShortenedURLs($dbConnection);
$serviceMeta = new ServiceMeta($dbConnection);

//
// Handle page actions
//
if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {
		case 'update':
			// Validate the owner of the site
			$siteID = $_REQUEST['siteID'];
			if ((int) $sideID > 0) {
				$requestedSite = $serviceSites->get(array(
					'ID' => $siteID,
					'userID' => $params['userID']
				));
				if (isset($requestedSite) && $requestedSite && sizeof($requestedSite) > 0) {
					$requestedSite = $requestedSite[0];
				}
			}
			break;
		default:
			break;
	}
}

//
// Set data params
//
// Grab the associated shortened site object
$successfullyGotSite = true;
$requestedShortenedSite = $serviceShortenedURLs->get(array(
	'shortURL' => $_REQUEST['shortURL'],
));
if (isset($requestedShortenedSite) && $requestedShortenedSite && sizeof($requestedShortenedSite) > 0) {
	// Now use that to get the site
	$requestedShortenedSite = $requestedShortenedSite[0];
	$requestedSite = $serviceSites->get(array(
		'ID' => $requestedShortenedSite['siteID'],
		'userID' => $params['userID']
	));
	if (isset($requestedSite) && $requestedSite && sizeof($requestedSite) > 0) {
		$requestedSite = $requestedSite[0];
		$params['site'] = $requestedSite;
		$params['site']['shortURL'] = $requestedShortenedSite['shortURL'];
		// Now get the meta tags
		$requestedMetaTags = $serviceMeta->get(array(
			'siteID' => $requestedSite['ID']
		));
		$params['metaTags'] = array();
		foreach ($requestedMetaTags as $metaKey => $metaValue) {
			$params['metaTags'][$metaValue['metaTag']] = $metaValue['metaValue'];
		}
	} else $successfullyGotSite = false;
} else $successfullyGotSite = false;

if (!$successfullyGotSite) {
	$params['errorMessages'][] = array(
		'role' => 'warning',
		'text' => 'We couldn\'t find this site, please <a href="/account">go back</a> to your account and try again.'
	);
}

//
// Output HTML
//
$output[] = $twig->render('account/site.twig', $params);
echo implode($output);