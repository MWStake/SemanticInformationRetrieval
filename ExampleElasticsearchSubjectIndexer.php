<?php

namespace SIR;

class ExampleElasticsearchSubjectIndexer {

	public function __construct($fullPageName) {
		$this->page = new Page($fullPageName)
	}

	public function indexPage() {
		$docId = $this->getDocumentExistsId();
		if($docId === false) {
			$this->addDocument();
		} else {
			$this->updateDocument($docId);
		}
	}

	private function addDocument() {
		$ch = new CurlTransfer(SUBJECTS_INDEX, "subject", NULL, "POST");
		$data = $this->getPageDataJSON();
		$ch->setPostFields($data);
		try {
			$response = json_decode($ch->exec(), true);
			error_log($this->page->fullPageName." added."."\n");
		} catch(Exception $e) {
			error_log($e->getMessage());
		}
	}

	private function updateDocument($docId) {
		$data = '
		{
			"doc": '.$this->getPageDataJSON().'
		}';
		try {
			$ch = new CurlTransfer(SUBJECTS_INDEX, "subject", $docId."/_update", "POST");
			$ch->setPostFields($data);
			$response = json_decode($ch->exec(), true);
		} catch(Exception $e) {
			error_log($e->getMessage());
		}
	}

	private function getPageDataJSON() {
		$pageDataArray = array(
			'subjectName' => $this->page->fullPageName,
			'subjectTitle' => Page::getSemanticMWPropertyInstanceValues($this->page->fullPageName, 'HasTypeAndTitle'),
			'subjectTitleNA' => Page::getSemanticMWPropertyInstanceValues($this->page->fullPageName, 'HasTypeAndTitle'),
			'subjectType' => Page::getSemanticMWPropertyInstanceValues($this->page->fullPageName, 'HasType'),
			'subjectLink' => $this->page->getPageURL(),
			'subjectProperties' => $this->getPageProperties()
		);
		return json_encode($pageDataArray);
	}

	private function getPageProperties() {
		$apiCall = array(
				'action' => 'browsebysubject',
				'subject'  => $this->page->fullPageName
			);
		$resultData = \API::apiCall($apiCall);
		$propertiesArray = $resultData['query']['data'];
		$propertiesArray2 = array();
		foreach($propertiesArray as $propertyData) {
			if(is_array($propertyData)) {
				$propertyName = $propertyData['property'];
				$propertyValuesArray = $propertyData['dataitem'];
				if(substr($propertyName, 0, 1) == "_") {

				} else {
					$property = new Property2($propertyName);
					$property->registerPropertyValues($propertyValuesArray);
					$propertiesArray2[] = array(
						'subjectPropertyName' => $property->propertyName,
						'subjectPropertyValues' => $property->propertyValues
					);
				}
			}
		}
		return $propertiesArray2;
	}

	private function getDocumentExistsId() {
		$pageName = str_replace(" ", "%20", $this->page->fullPageName);
		$ch = new CurlTransfer(SUBJECTS_INDEX, "subject", "_search?q=subjectName:%22'.$pageName.'%22", "GET");
		$response = json_decode($ch->exec(), true);
    if($response['hits']['total'] == 1) {
      return $response['hits']['hits'][0]['_id'];
    } else {
      return false;
    }
  }

}
