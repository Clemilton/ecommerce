<?php 
//Essa classe faz a interface entre o RainTPL e os arquivos .html


namespace Hcode;

use Rain\Tpl;

class Page {

	private $tpl;
	private $options = [];
	private $defaults = [
		"header"=>true,
		"footer"=>true,
		"data"=>[]

	];
	//$opts -> Variaveis que vao passar para o template
	public function __construct($opts = array(),$tpl_dir="/views/")
	{
		//Junta as informações padrões com as opções
		$this->options = array_merge($this->defaults, $opts);
		
		$config = array(
		    "tpl_dir"       => $_SERVER['DOCUMENT_ROOT'].$tpl_dir,
		    "cache_dir"     => $_SERVER['DOCUMENT_ROOT']."/views-cache/",
		    "debug"         => false
		);
		//Configuro template rainTPL
		Tpl::configure( $config );
		//cria uma instancia rainTPL
		$this->tpl = new Tpl;


		$this->setData($this->options['data']);

		if($this->options["header"]==true) // caso a pagina tenha header
			$this->tpl->draw("header");

	}
	
	public function __destruct()
	{

		if($this->options["footer"]==true) // caso a pagina tenha footer
			$this->tpl->draw("footer", false);

	}
	//Seta os valores das variaveis no template
	private function setData($data = array())
	{

		foreach($data as $key => $val)
		{

			$this->tpl->assign($key, $val);

		}

	}
	//Seta o c
	public function setTpl($tplname, $data = array(), $returnHTML = false)
	{

		$this->setData($data);

		return $this->tpl->draw($tplname, $returnHTML);

	}

}

 ?>