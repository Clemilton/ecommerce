<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model{

	
	//Listar as categorias
	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT* from tb_categories ORDER BY descategory");
	}

	public function save(){
		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));

		$this->setData($results[0]);


		Category::updateFile();

	}

	//Pesquisar um usario atraves de um ID
	public function get($idcategory){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory=:idcategory  ",array(
				":idcategory"=>$idcategory

			));

		$this->setData($results[0]);
	}

	public function delete(){

		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory=:idcategory",array(
			":idcategory"=>$this->getidcategory()
		));

		Category::updateFile();
	}

	public static function updateFile(){

		$categories= Category::listAll();
		var_dump($categories);
		$html = [];

		foreach ($categories as $row) {
			echo "ola ".$row['descategory'];
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}
		$str= implode('',$html);
		
		file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR."categories-menu.html", $str);

	}


}

?>