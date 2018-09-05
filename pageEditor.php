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

if ($site['userID'] > 0 && $params['userID'] != $site['userID']) {
    header('Location: /');
}

$originalURL = $site['URL'];

// Fetch tag suggestions
$params['suggestions'] = $serviceMeta->fetchMetaTagSuggestions($originalURL);
// Now we can fetch the metadata
// TODO:
//	[] Check database metadata first
$params['metadata'] = array();


// Fetch the tags in the database
$dbTags = $serviceMeta->get(array(
    'siteID' => $site['ID']
));

if (!isset($dbTags) || sizeof($dbTags) <= 0) {
    $fetchedMetaTags = $serviceMeta->fetchMetaTags($originalURL);
    // Process the tags
    foreach ($fetchedMetaTags as $key => $metaTag) {
        // Send to TWIG
        $params['metadata'][$key] = $metaTag;
        // Create the metatag object
        $tagObject = array(
            'metaTag' => $key,
            'metaValue' => $metaTag,
            'siteID' => $site['ID']
        );
        // Validate the object
        $validation = $serviceMeta->validate($tagObject);
        
        if ($validation['isValid']) {
            // See if the tag exists for this site
            $existingTag = $serviceMeta->get(array(
                'siteID' => $site['ID'],
                'metaTag' => $key
            ));
            
            if (isset($existingTag) && sizeof($existingTag) >= 1) {
                $existingTag = $existingTag[0];
                $existingTag['metaValue'] = $tagObject['metaValue'];
                
                $serviceMeta->create($existingTag, true);
            } else {
                $serviceMeta->create($tagObject, false);
            }
        }
    }
} else {
    // Send DB tags to TWIG
    foreach ($dbTags as $key => $tag) {
        $params['metadata'][$tag['metaTag']] = $tag['metaValue'];
    }
}

//
// Process special cases
//

// If Twitter image but no base image, set base
if (isset($params['metadata']['twitter:image'])) $params['metadata']['imageURL'] = $params['metadata']['twitter:image'];
// If OG image but no base, set base
if (isset($params['metadata']['og:image'])) $params['metadata']['imageURL'] = $params['metadata']['og:image'];
// If OG title but no base, set base 
if (isset($params['metadata']['og:title']) && !isset($params['metadata']['title'])) $params['metadata']['title'] = $params['metadata']['og:title'];
// If OG description but no base, set base
if (isset($params['metadata']['og:description']) && !isset($params['description'])) $params['metadata']['description'] = $params['metadata']['og:description'];
// If Twitter title but no base, set base 
if (isset($params['metadata']['twitter:title']) && !isset($params['title'])) $params['metadata']['title'] = $params['metadata']['twitter:title'];
// If Twitter description but no base, set base 
if (isset($params['metadata']['twitter:description']) && !isset($params['description'])) $params['metadata']['description'] = $params['metadata']['twitter:description'];
// If base title but no OG, set OG
if (isset($params['metadata']['title']) && !isset($params['metadata']['og:title'])) $params['metadata']['og:title'] = $params['metadata']['title'];
// If base image but no OG, set OG
if (isset($params['metadata']['imageURL']) && !isset($params['metadata']['og:image'])) $params['metadata']['og:image'] = $params['metadata']['imageURL'];


//
// Handle page actions
//
if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        //
        // Action for creating a site entry
        //
        case 'updateTags':
        // Gather all the meta tags
        
        // Basic tags
        $params['metadata']['title'] = isset($_REQUEST['metaTitle']) ? $_REQUEST['metaTitle'] : '';
        $params['metadata']['description'] = isset($_REQUEST['metaDescription']) ? $_REQUEST['metaDescription'] : '';
        $params['metadata']['imageURL'] = isset($_REQUEST['metaImageURL']) ? $_REQUEST['metaImageURL'] : '';
        $params['metadata']['keywords'] = isset($_REQUEST['metaKeywords']) ? $_REQUEST['metaKeywords'] : '';
        
        // Twitter tags
        $params['metadata']['twitter:card'] = isset($_REQUEST['metaTwitterCard']) ? $_REQUEST['metaTwitterCard'] : '';
        $params['metadata']['twitter:site'] = isset($_REQUEST['metaTwitterSite']) ? $_REQUEST['metaTwitterSite'] : '';
        $params['metadata']['twitter:creator'] = isset($_REQUEST['metaTwitterCreator']) ? $_REQUEST['metaTwitterCreator'] : '';
        
        // Open Graph tags
        $params['metadata']['og:title'] = isset($_REQUEST['metaOpenGraphTitle']) ? $_REQUEST['metaOpenGraphTitle'] : '';
        $params['metadata']['og:type'] = isset($_REQUEST['metaOpenGraphType']) ? $_REQUEST['metaOpenGraphType'] : '';
        $params['metadata']['og:image'] = isset($_REQUEST['metaOpenGraphImageURL']) ? $_REQUEST['metaOpenGraphImageURL'] : '';
        $params['metadata']['og:url'] = isset($_REQUEST['metaOpenGraphURL']) ? $_REQUEST['metaOpenGraphURL'] : '';
        $params['metadata']['fb:app_id'] = isset($_REQUEST['metaFacebookAppID']) ? $_REQUEST['metaFacebookAppID'] : '';
        $params['metadata'] = $serviceMeta->fillMissingMeta($params['metadata']);
        // Process the tags
        foreach ($params['metadata'] as $key => $metaTag) {
            // Create the metatag object
            $tagObject = array(
                'metaTag' => $key,
                'metaValue' => $metaTag,
                'siteID' => $site['ID']
            );
            // Validate the object
            $validation = $serviceMeta->validate($tagObject);
            
            if ($validation['isValid']) {
                // See if the tag exists for this site
                $existingTag = $serviceMeta->get(array(
                    'siteID' => $site['ID'],
                    'metaTag' => $key
                ));
                
                if (isset($existingTag) && sizeof($existingTag) >= 1) {
                    $existingTag = $existingTag[0];
                    $existingTag['metaValue'] = $tagObject['metaValue'];
                    
                    $serviceMeta->create($existingTag, true);
                } else {
                    $serviceMeta->create($tagObject, false);
                }
            }
        }
        $params['errorMessages'][] = array(
            'role' => 'success',
            'text' => 'Meta data updated successfully 👌'
        );
        
        break;
    }
}

//
// Output HTML
//
$output[] = $twig->render('pageEditorMin.twig', $params);
echo implode($output);