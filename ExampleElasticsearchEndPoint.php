<?php

namespace SIR;

# CONSTANTS are placeholders for endpoint configuration coming from somewhere.

require_once('CurlTransfer.php');

class ExampleElasticsearchEndPoint {

  public function __construct($q) {
    $this->allKeywordsFound = array();
    $this->q = $q;
	}

  public function getSubjects() {
    $subjects = $this->getSubjects2();
    uksort($this->allKeywordsFound, "strnatcasecmp");
    return "<p class=''>Keywords found: ".implode(' &middot; ', $this->allKeywordsFound)."<p>
    <table class=''>
    <!--thead><tr>
      <th width='50%'>Subject</th>
      <th>Properties</th>
    </tr></thead-->
    <tbody>".implode("", $subjects)."
    </tbody></table><p>Retrieved from ".SUBJECTS_INDEX."</p>";
  }

  public function getSubjects2() {
    $allSubjectsHavingValueInProperty = '
    {
      "size": 1000,
      "query": {
        "nested": {
          "path": "subjectProperties",
          "query": {
            "bool": {
              "must": [
                {
                  "nested": {
                    "path": "subjectProperties.subjectPropertyValues",
                    "query": {
                      "query_string": {
                        "default_field": "subjectProperties.subjectPropertyValues.subjectPropertyValue",
                        "query": "'.$this->q.'*"
                      }
                    }
                  }
                },
                {
                  "match": {
                    "subjectProperties.subjectPropertyName": "HasKeyword"
                  }
                }
              ]
            }
          }
        }
      }
    }
    ';
    $ch = new CurlTransfer(SUBJECTS_INDEX, "subject", "_search", "POST");
    $ch->setPostFields($allSubjectsHavingValueInProperty);
    $responseArray = json_decode($ch->exec(), true);
    $i = 1;
    foreach($responseArray['hits']['hits'] as $hit) {
      $subjects[] = "<tr>
          <td width='50%'><hr><a href='".MW_ARTICLE_PATH."/".$hit['_source']['subjectName']."'>".$hit['_source']['subjectTitle']."</a></td>
          <td>".implode("<br>", $this->listProperties($hit['_source']['subjectProperties']))."</td>
        </tr>";
      $i++;
    }
    # id='example' class='display'
    return $subjects;
  }

  private function listProperties($propertiesArray) {
    $arr = array();
    $excludeTheseProps = array("HasType", "HasTypeAndTitle");
    foreach($propertiesArray as $propertyData) {
      if(!in_array($propertyData['subjectPropertyName'], $excludeTheseProps)) {
        $propLink = "<a href='".MW_ARTICLE_PATH."/Property:".$propertyData['subjectPropertyName']."'>".$propertyData['subjectPropertyName']."</a>";
        $arr[] = "<span class='PropName'>".$propLink."</span> <span class='PropVal'>".$this->listPropertyValues($propertyData)."</span>";
      }
    }
    return $arr;
  }

  private function listPropertyValues($propertyData) {
    $vals = array();
    foreach($propertyData['subjectPropertyValues'] as $value) {
      if($propertyData['subjectPropertyName'] == 'HasKeyword') {
        $val = $this->highlight($this->q, $value['subjectPropertyValue']);
      } else {
        $val = $value['subjectPropertyValue'];
      }
      $vals[] = $val;
    }
    return implode(", ", $vals);
  }

  private function highlight($whatToHighlight, $inThisString) {
    # $whatToHighlight is lowercase
    # $inThisString is mixed case
    if(stripos($inThisString, $whatToHighlight) === false) {
      $ret = $inThisString;
    } else {
      $ret = str_ireplace($whatToHighlight, "<span class=''>".$whatToHighlight."</span>", $inThisString);
      $this->allKeywordsFound[$inThisString] = "<a class='PropVal' href='".MW_ARTICLE_PATH."/".$inThisString."'>".$ret."</a>";
    }
    return "<a href='".MW_ARTICLE_PATH."/".$inThisString."'>".$ret."</a>";
  }

}

$smwckep = new ExampleElasticsearchEndPoint($_POST['q']);
echo $smwckep->getSubjects();
