<?php

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';
require_once $basePath . '/maintenance/Maintenance.php';

class ExampleResetSubjectsIndex extends Maintenance {

        private $keywords = array();

        public function __construct() {
                parent::__construct();
                global $wgServer;
                global $globalLog;
                global $wgScriptPath;
        }

        public function execute() {

                # Delete
                $ch = new CurlTransfer(SUBJECTS_INDEX, NULL, NULL, "DELETE");
                $ch->exec();

                # Create
                $options = '
                  {
                      "settings" : {
                          "index" : {
                              "number_of_shards" : 1,
                              "number_of_replicas" : 1
                          }
                      }
                  }
                  ';
                $ch = new CurlTransfer(SUBJECTS_INDEX, NULL, NULL, "PUT");
                $ch->setPostFields($options);
                $ch->exec();

                # Mapping
                $mapping = '
                {
                  "subject": {
                    "properties": {
                      "subjectName": {
                        "type": "string",
                        "include_in_all": "true"
                      },
                      "subjectTitle": {
                        "type": "string"
                      },
                      "subjectTitleNA": {
                        "type": "string",
                        "store": "true",
                        "index": "not_analyzed",
                        "include_in_all": "true"
                      },
                      "subjectType": {
                        "type": "string",
                        "index": "not_analyzed"
                      },
                      "subjectLink": {
                        "type": "string",
                        "index": "not_analyzed"
                      },
                      "subjectProperties": {
                        "type": "nested",
                        "include_in_parent": "true",
                        "properties": {
                          "subjectPropertyName": {
                            "type": "string",
                            "index": "not_analyzed",
                            "include_in_all": "true"
                          },
                          "subjectPropertyValues": {
                            "type": "nested",
                            "properties": {
                              "subjectPropertyValue": {
                                "type": "string",
                                "store": "true",
                                "include_in_all": "true"
                              },
                              "subjectPropertyValueNA": {
                                "type": "string",
                                "store": "true",
                                "index": "not_analyzed",
                                "include_in_all": "true"
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
                ';
                $ch = new CurlTransfer(SUBJECTS_INDEX, "subject", "_mapping", "PUT");
                $ch->setPostFields($mapping);
                try {
                  $response = json_decode($ch->exec(), true);
                } catch(Exception $e) {
                  error_log($e->getTrace());
                }
        }
}
$maintClass = 'ExampleResetSubjectsIndex';
require_once RUN_MAINTENANCE_IF_MAIN;
