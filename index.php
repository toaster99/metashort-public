<?php
/** 
* Includes
**/
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/environment.php';

//
// Services
//
$serviceSites = new ServiceSites($dbConnection);
$serviceShortenedURLs = new ServiceShortenedURLs($dbConnection);
$serviceMeta = new ServiceMeta($dbConnection);

$output = array();

//
// Check for producthunt
//
$params['ref'] = isset($_REQUEST['ref']) ? $_REQUEST['ref'] : '';

//
// Grab recent links/meta (no need for short version)
//
$recentSiteObjects = $serviceSites->get(array(), array(
    'limit' => 100,
    'orderbyid' => 'DESC'
));

$params['recentSites'] = array();

foreach ($recentSiteObjects as $key => $value) {
    $processedSiteObject = $value;
    // Grab the meta data for the site object
    $metaTags = $serviceMeta->get(array(
        'siteID' => $processedSiteObject['ID']
    ));

    // Set the url
    $processedSiteObject['meta']['url'] = 'metashort.co';
    foreach ($metaTags as $key => $metaValue) {
        $processedSiteObject['meta'][$metaValue['metaTag']] = $metaValue['metaValue'];
    }

    if (!$processedSiteObject['meta']['title'] && !$processedSiteObject['meta']['twitter:title'] && !$processedSiteObject['meta']['og:title']) continue;
    if (!$processedSiteObject['meta']['imageURL'] && !$processedSiteObject['meta']['twitter:image'] && !$processedSiteObject['meta']['og:image:url']) continue;
    if (!$processedSiteObject['meta']['description'] && !$processedSiteObject['meta']['twitter:description'] && !$processedSiteObject['meta']['og:description']) continue;
    
    $params['recentSites'][] = $processedSiteObject;

    if (sizeof($params['recentSites']) >= 3) break;
}



//
// Handle page actions
//
if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        //
        // Action for creating a site entry
        //
        case 'createSite':
        $url = $_REQUEST['headerLink'];
        if (!preg_match('/^(http:\/\/|https:\/\/).*/', $url)) $url = 'http://' . $url;
            $site = array(
                'URL' => $url
            );
            
            // If user is logged in, associate thier ID
            if ($params['loggedIn']) $site['userID'] = $params['userID'];
            else $site['userID'] = 0;
            
            // Validate the site
            $validation = $serviceSites->validate($site);
            if ($validation['isValid']) {
                //Create the site
                $site = $serviceSites->create($site);
                // Generate the short URL
                $shortenedURL = $serviceShortenedURLs->generateUniqueURL(5, $restrictedLinkPaths);
                $shortenedURLObject = array(
                    'siteID' => $site['ID'],
                    'shortURL' => $shortenedURL,
                );
                $validation = $serviceShortenedURLs->validate($shortenedURLObject);
                if ($validation['isValid']) {
                    // Create the shortened URL
                    $shortenedURLObject = $serviceShortenedURLs->create($shortenedURLObject);
                    header('Location: /' . $shortenedURL . '/edit');
                    exit();
                }
            }
            break;
        }
    }
    
    //
    // Output HTML
    //
    $output[] = $twig->render('indexMin.twig', $params);
    // else $output[] = $twig->render('index.twig', $params);
    echo implode($output);