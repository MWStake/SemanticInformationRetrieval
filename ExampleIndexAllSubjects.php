<?php

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';
require_once $basePath . '/maintenance/Maintenance.php';

class ExampleIndexAllSubjects extends Maintenance {

  private $keywords = array();

  public function __construct() {
    parent::__construct();
    global $wgServer;
    global $globalLog;
    global $wgScriptPath;
  }

  public function execute() {
    $apiCall = array(
        'action' => 'ask',
        'query'  => '[[Category:Subject]]|limit=500'
      );
    $resultData = \API::apiCall($apiCall);
    foreach($resultData['query']['results'] as $page => $pageData) {
      $sind = new ExampleElasticsearchSubjectIndexer($page);
      $sind->indexPage();
    }
  }
}

$maintClass = 'ExampleIndexAllSubjects';
require_once RUN_MAINTENANCE_IF_MAIN;
