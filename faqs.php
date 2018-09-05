<?php
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
// Output HTML
//
$output[] = $twig->render('faqs.twig', $params);
echo implode($output);