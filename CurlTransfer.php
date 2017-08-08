<?php

namespace SIR;

class CurlTransfer {

	public function __construct($index, $type, $endpoint, $method) {
		$args = func_get_args();
		$method = array_pop($args);
		foreach($args as $arg) {
			if(!empty($arg)) {
				$path_comps[] = $arg;
			}
		}
		$path = SMWCK_ELASTIC_SERVER."/".implode("/", $path_comps);
		$this->ch = curl_init($path);
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
	}

	public function setPostFields($fields) {
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $fields);
	}

	public function exec() {
		$response = curl_exec($this->ch);
		curl_close($this->ch);
		return $response;
	}

}
