<?php 
ini_set('user_agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:57.0) Gecko/20100101 Firefox/57.0');
header('Content-Type: application/json');
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/environment.php';

$errors = array();
$results = array();

$url = isset($_REQUEST['url']) ? $_REQUEST['url'] : NULL;

if (!isset($url) || !filter_var($url, FILTER_VALIDATE_URL)) $errors[] = 'Invalid URL format.';
else {
	$metaTags = get_meta_tags($url);
	if (!isset($metaTags)) $errors[] = 'No meta tags found.';
	else {
		$results = $metaTags;
	}

	$doc = new DOMDocument();
	@$doc->loadHTMLFile($url);
	$xpath = new DOMXPath($doc);

	if (isset($doc) && isset($xpath)) {
		$title = $xpath->query('//title')->item(0)->nodeValue;
		if (isset($title)) $results['title'] = $title;
	}

	if (isset($results['twitter:image'])) $results['imageURL'] = $results['twitter:image'];
	if (isset($results['og:image'])) $results['imageURL'] = $results['og:image'];
}


echo json_encode(array(
	'errors' => $errors,
	'results' => $results
));