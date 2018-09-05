<?php
// 
// Includes
//
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/environment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/services/serviceSites.php';
//
// Headers
//
header("HTTP/1.1 200 OK");
header("Content-Type: text/html");

//
// Services
//
$serviceSites = new ServiceSites($dbConnection);
$serviceShortenedURLs = new ServiceShortenedURLs($dbConnection);
$serviceMeta = new ServiceMeta($dbConnection);
$serviceClickTracking = new ServiceClickTracking($dbConnection);

$output = array();

$output[] = '<html><head>';

//
// Lookup the destination page
//
if (isset($_REQUEST['id'])) {
	// Lookup the shortened URL
	$shortenedURLResult = $serviceShortenedURLs->get(array(
		'shortURL' => $_REQUEST['id']
	));
	if (sizeof($shortenedURLResult) > 0) {
		$shortenedURLResult = $shortenedURLResult[0];
		// Lookup the associated site entry
		$siteResult = $serviceSites->get(array(
			'ID' => $shortenedURLResult['siteID']
		))[0];
		// Get the meta tags
		$metaTags = $serviceMeta->get(array(
			'siteID' => $siteResult['ID']
		));
		// Write tags to the page
		if ($params['isDevServer']) $shortenedURL = 'http://metashort.dev/' . $_REQUEST['id'];
		else $shortenedURL = 'http://metashort.co/' . $_REQUEST['id'];
		foreach ($metaTags as $tag) {
			if ($tag['metaValue'] == '') continue;
			if ($tag['metaTag'] == 'title') {
				$output[] = '<title>' . $tag['metaValue'] . '</title>';
				$output[] = '<meta name="twitter:title" content="' . $tag['metaValue'] . '">';	
			} elseif ($tag['metaTag'] == 'description') {
				$output[] = '<meta name="description" content="' . $tag['metaValue'] . '">';	
				$output[] = '<meta name="twitter:description" content="' . $tag['metaValue'] . '">';	
			} elseif ($tag['metaTag'] == 'imageURL') {
				$output[] = '<meta name="image" content="' . $tag['metaValue'] . '">';	
				$output[] = '<meta name="twitter:image" content="' . $tag['metaValue'] . '">';	
			} elseif (strpos($tag['metaTag'], 'og:') !== FALSE) {
				$output[] = '<meta property="' . $tag['metaTag'] . '" content="' . $tag['metaValue'] . '">';	
			}
			if ($tag['metaTag'] == 'og:image') {
				$output[] = '<meta property="og:image:url" content="' . $tag['metaValue'] . '">';	
			}
			else $output[] = '<meta name="' . $tag['metaTag'] . '" content="' . $tag['metaValue'] . '">';
		}

		// Write redirect code to the DOM
		$output[] = '<a href="http://metashort.co">MetaShort</a> is redirecting you to <a href="' . $siteResult['URL'] . '">' . $siteResult['URL'] . '</a>';
		$output[] = '<script>';
		// $output[] = 'window.location = "' . $siteResult['URL'] . '";';
		$output[] = '</script>';
		$output[] = "<script async src='https://www.googletagmanager.com/gtag/js?id=UA-58825605-10'></script>";
		$output[] = "<script>";
		$output[] = "window.dataLayer = window.dataLayer || [];";
		$output[] = "function gtag(){dataLayer.push(arguments)};";
		$output[] = "gtag('js', new Date());";
		$output[] = "gtag('config', 'UA-58825605-10');";
		$output[] = "</script>";

		// Log the click for analytics

	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $clientIP = $_SERVER['HTTP_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { 
        $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else $clientIP = $_SERVER['REMOTE_ADDR'];

		$clickTracking = array(
			'siteID' => $siteResult['ID'],
			'ipAddress' => $clientIP,
			'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''
		);

		// Validate the object
		$validation = $serviceClickTracking->validate($clickTracking);

		if ($validation['isValid']) {
			$clickTracking = $serviceClickTracking->create($clickTracking);
		}
	} else {
		echo 'Bad site';
	}
} else {
	echo 'Bad site';
}

$output[] = '</head></html>';

//
// Output HTML
//
echo implode($output);