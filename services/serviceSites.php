<?php

class ServiceSites {
  protected $connection;

  function __construct($dbConnection) {
    $this->connection = $dbConnection;
  }

  //
  // Validate site object
  //
  public function validate($site) {
    $validationMessages = array();

    if (!isset($site['userID']) || $site['userID'] < 0) $validationMessages[] = 'Invalid user ID';
    if (!isset($site['URL']) || !filter_var($site['URL'], FILTER_VALIDATE_URL)) $validationMessages[] = 'Invalid URL';

    return array(
      'isValid' => sizeof($validationMessages) <= 0,
      'validationMessages' => $validationMessages
    );
  }

  //
  // Insert a site into the db
  //
  public function create($site) {
    $params = array(
      'userID' => $site['userID'],
      'URL' => $site['URL']
    );

    $sqlQuery  = 'INSERT INTO sites ';
    $sqlQuery .= 'SET userID = :userID, ';
    $sqlQuery .= 'URL = :URL, ';
    $sqlQuery .= 'timeUpdated = NOW(), ';
    $sqlQuery .= 'timeCreated = NOW() ';

    $statement = $this->connection->prepare($sqlQuery);
    $createResult = $statement->execute($params);

    $site['ID'] = $this->connection->lastInsertId();

    return $site;
  }


  //
  // Gets a site from the specified criteria
  //
  public function get($site, $options = array()) {
    $params = array();

    $sqlQuery  = 'SELECT * FROM sites ';
    $sqlQuery .= 'WHERE 1 ';

    if (isset($site['ID'])) {
      $params['ID'] = (int) $site['ID'];
      $sqlQuery .= 'AND ID = :ID ';
    }

    if (isset($site['userID'])) {
      $params['userID'] = (int) $site['userID'];
      $sqlQuery .= 'AND userID = :userID ';
    }

    if (isset($options['valid_basic_tags'])) {
      $sqlQuery .= 'AND ID IN ( ';
      $sqlQuery .= 'SELECT siteID from sitesMeta WHERE metaTag IN ("imageURL","twitter:image","og:image:url") AND metaValue !="" AND siteID IN ';
      $sqlQuery .= '(SELECT siteID from sitesMeta WHERE metaTag IN ("title","twitter:title","og:title") AND metaValue !="" AND siteID IN ';
      $sqlQuery .= '(SELECT siteID from sitesMeta WHERE metaTag IN ("description","twitter:description","og:description") AND metaValue !="") ';
      $sqlQuery .=')) ';
    }

    if (isset($options['orderbyid'])) {
      $sqlQuery .= 'ORDER BY ID ' . ($options['orderbyid'] == 'DESC' ? 'DESC' : 'ASC') . ' ';
    }

    if (isset($options['limit'])) {
      $sqlQuery .= 'LIMIT ' . (int) $options['limit'] . ' ';
    }
    
    $statement = $this->connection->prepare($sqlQuery);
    $getResult = $statement->execute($params);
    return $statement->fetchAll();
  }

  //
  // Deletes the specified site
  //
  public function delete($site) {
    $params = array(
      'ID' => $site['ID']
    );

    $sqlQuery  = 'DELETE FROM sites ';
    $sqlQuery .= 'WHERE ID = :ID ';

    $statement = $this->connection->prepare($sqlQuery);
    $deleteResult = $statement->execute($params);

    return $deleteResult;
  }
}


