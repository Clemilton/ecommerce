<?php
//Classe para ajudar nos getters and setters
namespace Hcode;

class Model {

	private $values = [];

	public function __call($name,$args){

		$method = substr($name, 0,3);

		$field = substr($name, 3,strlen($name));

		switch ($method) {
			case 'get':
				return $this->values[$field];
				break;
			
			case 'set':
				$this->values[$field]=$args[0];
				break;
		}
	}

	public function setData($data = array()){
		//Seta os dados vindos do array $data
		foreach ($data as $key => $value) {
			$this->{"set".$key}($value);

		}
	}

	public function getValues(){
		return $this->values;
	}
}



?>