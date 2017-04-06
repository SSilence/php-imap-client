<?php

namespace SSilence\ImapClient;

class IncomingMessageAttachment {

	private $rawObject;

	public function __construct ($rawObject) {
		$this->rawObject = $rawObject;
	}

	public function getRaw () {
		return $this->rawObject;
	}

	private $name;
	public function getName () {
		if ($this->name === null) {
			foreach ($this->rawObject->structure->dparameters as $param) {
				if ($param->attribute == 'filename') {
					$this->name = $param->value;
					break;
				};
			};
		};
		return $this->name;
	}

	private $body;
	public function getBody () {
		if ($this->body === null) {
			$this->body = $this->rawObject->body;
		};
		return $this->body;
	}

};
