<?php

class ServiceMeta {
  protected $connection;
  protected $validMetaTags = array(
    'title',
    'description',
    'imageURL',
    'image',
    'keywords',
    'twitter:card',
    'twitter:site',
    'twitter:creator',
    'twitter:image',
    'og:image:url',
    'og:title',
    'og:type',
    'og:image',
    'og:url',
    'fb:app_id'
  );
  
  function __construct($dbConnection) {
    $this->connection = $dbConnection;
  }
  
  //
  // Meta tag fetcher
  //
  public function fetchMetaTags($url) {
    // Change URL to non https
    $url = preg_replace('/^https:/i', 'http:', $url);
    // Grab the document
    $doc = new DOMDocument();
    @$doc->loadHTMLFile($url);
    $xpath = new DOMXPath($doc);

    if (!isset($xpath) || !isset($doc)) return;

    // Iterate through valid tags and fetch them
    $fetchedTags = array();
    foreach ($this->validMetaTags as $tagKey => $tagValue) {
      // Look for the content attribute of description meta tags 
      $contents = $xpath->query('/html/head/meta[@name="' . $tagValue .'"]/@content');
      if ($contents-> length <= 0) $contents = $xpath->query('/html/head/meta[@property="' . $tagValue .'"]/@content');
      if ($contents-> length <= 0) $contents = $xpath->query('/html/head/'. $tagValue);


      // If nothing matches the query
      if (!is_null($contents) && isset($contents) && is_object($contents) && $contents->length > 0) {
          foreach ($contents as $content) {
              $fetchedTags[$tagValue] = $content->nodeValue;
          }
      }
    }

    // Fill missing deta
    $fetchedTags = $this->fillMissingMeta($fetchedTags);

    return $fetchedTags;
  }

  //
  // Meta tag suggestion engine
  //
  public function fetchMetaTagSuggestions($url, $suggestionMax = 5) {
    // Change URL to non https
    $url = preg_replace('/^https:/i', 'http:', $url);
    // Grab the document
    $doc = new DOMDocument();
    @$doc->loadHTMLFile($url);
    $xpath = new DOMXPath($doc);

    if (!isset($xpath) || !isset($doc)) return;
    // Grab alternative title suggestions
    $possibleTitles = array(
      '//h1',
      '//*[contains(concat(" ", normalize-space(@class), " "), " title ")]',
      '//h2'
    );
    $tagSuggestions = array();
    $titleLength = 60;
    $tagSuggestions['title'] = array();
    foreach ($possibleTitles as $titleQuery) {
      $qResult = $xpath->query($titleQuery);
      if ($qResult->length >= 1) {
        foreach ($qResult as $title) {
          if (trim($title->nodeValue) != '') {
            if (strlen($title->nodeValue) > $titleLength) {
              $trimmed = substr($title->nodeValue,0, 157) . '...';
              $tagSuggestions['title'][] = $trimmed;
            } else {
              $tagSuggestions['title'][] = $title->nodeValue;
            }
          }
          if (sizeof($tagSuggestions['title']) >= $suggestionMax) break;
        }
      }
    }

    // Grab alternative descriptions suggestions
    $possibleDescriptions = array(
      '//p',
      '//*[contains(concat(" ", normalize-space(@class), " "), " content ")]',
      '//*[contains(concat(" ", normalize-space(@class), " "), " body ")]'
    );
    $descriptionLength = 160;
    $tagSuggestions['description'] = array();
    foreach ($possibleDescriptions as $titleQuery) {
      $qResult = $xpath->query($titleQuery);
      if ($qResult->length >= 1) {
        foreach ($qResult as $description) {
          if (trim($description->nodeValue) != '') {
            if (strlen($description->nodeValue) > $descriptionLength) {
              $trimmed = substr($description->nodeValue,0, 157) . '...';
              $tagSuggestions['description'][] = $trimmed;
            } else {
              $tagSuggestions['description'][] = $description->nodeValue;
            }
          }
          if (sizeof($tagSuggestions['description']) >= $suggestionMax) break;
        }
      }
    }

    // Grab alternative descriptions suggestions
    $possibleImages = array(
      '//img/@src',
    );
    $tagSuggestions['images'] = array();
    foreach ($possibleImages as $imageQuery) {
      $qResult = $xpath->query($imageQuery);
      if ($qResult->length >= 1) {
        foreach ($qResult as $image) {
          if (trim($image->nodeValue) != '') $tagSuggestions['images'][] = $image->nodeValue;
          if (sizeof($tagSuggestions['images']) >= $suggestionMax) break;
        }
      }
    }

    return $tagSuggestions;
  }

  //
  // Be smart and infer tags that are missing
  //
  public function fillMissingMeta($metaTags) {
    // Title
    if ($metaTags['title']) $title = $metaTags['title'];
    elseif ($metaTags['og:title']) $title = $metaTags['og:title'];
    elseif ($metaTags['twitter:title']) $title = $metaTags['twitter:title'];
    
    if (!isset($metaTags['title']) || trim($metaTags['title']) == '') $metaTags['title'] = $title;
    if (!isset($metaTags['twitter:title']) || trim($metaTags['twitter:title']) == '') $metaTags['twitter:title'] = $title;
    if (!isset($metaTags['og:title']) || trim($metaTags['og:title']) == '') $metaTags['og:title'] = $title;

    // Description
    if ($metaTags['description']) $description = $metaTags['description'];
    elseif ($metaTags['og:description']) $description = $metaTags['og:description'];
    elseif ($metaTags['twitter:description']) $description = $metaTags['twitter:description'];
    
    if (!isset($metaTags['description']) || trim($metaTags['description']) == '') $metaTags['description'] = $description;
    if (!isset($metaTags['twitter:description']) || trim($metaTags['twitter:description']) == '') $metaTags['twitter:description'] = $description;
    if (!isset($metaTags['og:description']) || trim($metaTags['og:description']) == '') $metaTags['og:description'] = $description; 

    // Image
    if ($metaTags['image']) $image = $metaTags['image'];
    elseif ($metaTags['imageURL']) $image = $metaTags['imageURL'];
    elseif ($metaTags['twitter:image']) $image = $metaTags['twitter:image'];
    elseif ($metaTags['og:image:url']) $image = $metaTags['og:image:url'];
    
    if (!isset($metaTags['imageURL']) || trim($metaTags['imageURL']) == '') $metaTags['imageURL'] = $image;
    if (!isset($metaTags['twitter:image']) || trim($metaTags['twitter:image']) == '') $metaTags['twitter:image'] = $image;
    if (!isset($metaTags['og:image:url']) || trim($metaTags['og:image:url']) == '') $metaTags['og:image:url'] = $image;

    return $metaTags;
  }


  //
  // Validate meta object
  //
  public function validate($meta) {
    $validationMessages = array();
    
    if (!isset($meta['metaTag']) || !in_array($meta['metaTag'], $this->validMetaTags)) $validationMessages[] = 'Invalid meta tag';
    if (!isset($meta['metaValue']) || sizeof($meta['metaValue']) <= 0) $validationMessages[] = 'Invalid meta value';
    if (!isset($meta['siteID']) || (int) $meta['siteID'] <= 0) $validationMessages[] = 'Invalid site specified';
    
    return array(
      'isValid' => sizeof($validationMessages) <= 0,
      'validationMessages' => $validationMessages
    );
  }
  
  //
  // Insert a meta tag into the db
  //
  public function create($meta, $update = false) {
    $params = array(
      'metaTag' => $meta['metaTag'],
      'metaValue' => $meta['metaValue'],
      'siteID' => (int) $meta['siteID']
    );
    if ($update) $params['ID'] = $meta['ID'];
    
    if ($update) $sqlQuery = 'UPDATE sitesMeta ';
    else $sqlQuery  = 'INSERT INTO sitesMeta ';
    $sqlQuery .= 'SET metaTag = :metaTag, ';
    $sqlQuery .= 'metaValue = :metaValue, ';
    $sqlQuery .= 'siteID = :siteID, ';
    $sqlQuery .= 'timeUpdated = NOW(), ';
    $sqlQuery .= 'timeCreated = NOW() ';
    if ($update) $sqlQuery .= 'WHERE ID = :ID ';
    
    $statement = $this->connection->prepare($sqlQuery);
    $createResult = $statement->execute($params);
    
    if (!$update) $meta['ID'] = $this->connection->lastInsertId();
    
    return $meta;
  }
  
  //
  // Get meta objects based on the specified criteria
  //
  public function get($meta) {
    $params = array();
    
    $sqlQuery  = 'SELECT * FROM sitesMeta ';
    $sqlQuery .= 'WHERE 1 ';
    
    if (isset($meta['ID'])) {
      $params['ID'] = $meta['ID'];
      $sqlQuery .= 'AND ID = :ID ';
    }
    
    if (isset($meta['siteID'])) {
      $params['siteID'] = $meta['siteID'];
      $sqlQuery .= 'AND siteID = :siteID ';
    }
    
    if (isset($meta['metaTag'])) {
      $params['metaTag'] = $meta['metaTag'];
      $sqlQuery .= 'AND metaTag = :metaTag ';
    }
    
    $statement = $this->connection->prepare($sqlQuery);
    $getResult = $statement->execute($params);
    return $statement->fetchAll();
  }
  
  public function delete($meta) {
    $params = array(
      'ID' => $meta['ID']
    );
    
    $sqlQuery  = 'DELETE FROM sitesMeta ';
    $sqlQuery .= 'WHERE ID = :ID ';
    
    $statement = $this->connection->prepare($sqlQuery);
    $deleteResult = $statement->execute($params);
    
    return $deleteResult;
  }
}